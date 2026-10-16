<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\Empresas\Empresa;
use App\Models\Empresas\EmpresaSuscripcion;
use App\Models\Empresas\EmpresaComponentesSuscripcion;
use App\Http\Services\SuscripcionPagoLinkService;
use App\Helpers\Eco\SendEcoWhatsApp;
use App\Http\Controllers\Configuracion\SuscripcionController;

class CobroSuscripcionMensual extends Command
{
    /**
     * Uso:
     *   php artisan suscripcion:cobrar
     *   php artisan suscripcion:cobrar --empresa=55
     *   php artisan suscripcion:cobrar --empresa=55,60,72
     *   php artisan suscripcion:cobrar --dry-run
     *   php artisan suscripcion:cobrar --meses=1
     */
    protected $signature = 'suscripcion:cobrar
                            {--empresa=* : ID(s) de empresa. Acepta "55", "55,60" o repetir el flag.}
                            {--meses=1  : Cantidad de meses a cobrar (1-24).}
                            {--dry-run  : Simula sin enviar WhatsApp.}';

    protected $description = 'Envía el cobro mensual por WhatsApp a las empresas con cobro vencido.';

    /** @var SuscripcionPagoLinkService */
    protected $linkService;

    /** @var SuscripcionController */
    protected $controller;

    public function __construct(
        SuscripcionPagoLinkService $linkService,
        SuscripcionController $controller
    ) {
        parent::__construct();
        $this->linkService = $linkService;
        $this->controller  = $controller;
    }

    public function handle(): int
    {
        $ids    = $this->parsearIds($this->option('empresa'));
        $dryRun = (bool) $this->option('dry-run');
        $meses  = max(1, min(24, (int) $this->option('meses')));
        $hoy    = Carbon::today();

        $this->info('════════════════════════════════════════════════');
        $this->info('  COBRO MENSUAL DE SUSCRIPCIÓN');
        $this->info('  Fecha:    ' . $hoy->toDateString());
        $this->info('  Modo:     ' . ($dryRun ? 'DRY-RUN (no envía)' : 'REAL'));
        $this->info('  Meses:    ' . $meses);
        $this->info('  Empresas: ' . ($ids ? implode(', ', $ids) : 'TODAS'));
        $this->info('════════════════════════════════════════════════');

        $query = Empresa::query()
            ->where('estado', Empresa::ESTADO_ACTIVO)
            ->with(['componentes.componente']);

        if (! empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $totalEmpresas = (clone $query)->count();

        if ($totalEmpresas === 0) {
            $this->warn('No hay empresas activas para procesar.');
            return self::SUCCESS;
        }

        $this->info("Empresas activas a procesar: {$totalEmpresas}");
        $this->newLine();

        $enviados = 0;
        $saltados = 0;
        $errores  = 0;

        $query->chunkById(10, function ($empresas) use (
            &$enviados, &$saltados, &$errores, $dryRun, $meses, $hoy
        ) {
            foreach ($empresas as $empresa) {
                $r = $this->procesarEmpresa($empresa, $hoy, $meses, $dryRun);

                switch ($r['estado']) {
                    case 'enviado':
                        $enviados++;
                        $this->line("  ✅ #{$empresa->id} {$empresa->razon_social} → enviado (\${$r['total']})");
                        break;
                    case 'saltado':
                        $saltados++;
                        $this->line("  ⏭️  #{$empresa->id} {$empresa->razon_social} → saltado ({$r['motivo']})");
                        break;
                    case 'error':
                        $errores++;
                        $this->error("  ❌ #{$empresa->id} {$empresa->razon_social} → {$r['motivo']}");
                        break;
                }
            }
        });

        $this->newLine();
        $this->info('════════════════════════════════════════════════');
        $this->info("  Enviados:  {$enviados}");
        $this->info("  Saltados:  {$saltados}");
        $this->info("  Errores:   {$errores}");
        $this->info('════════════════════════════════════════════════');

        return self::SUCCESS;
    }

    /**
     * @return array{estado: string, motivo?: string, total?: float}
     */
    protected function procesarEmpresa(Empresa $empresa, Carbon $hoy, int $meses, bool $dryRun): array
    {
        try {
            // 1. Suscripción activa
            $suscripcion = EmpresaSuscripcion::where('id_empresa', $empresa->id)
                ->where('estado', '1')
                ->first();

            if (! $suscripcion) {
                return ['estado' => 'saltado', 'motivo' => 'sin suscripción activa'];
            }

            // 2. Componentes de esa suscripción
            $componentes = EmpresaComponentesSuscripcion::with('componente')
                ->where('id_empresa', $empresa->id)
                ->where('id_empresa_suscripcion', $suscripcion->id)
                ->has('componente')
                ->get();

            if ($componentes->isEmpty()) {
                return ['estado' => 'saltado', 'motivo' => 'sin componentes'];
            }

            // 3. ¿Hay algo vencido o que vence hoy?
            $vencidos = $componentes->filter(function ($c) use ($hoy) {
                if (! $c->fecha_siguiente_cobro) return false;
                return Carbon::parse($c->fecha_siguiente_cobro)->lte($hoy);
            });

            if ($vencidos->isEmpty()) {
                return ['estado' => 'saltado', 'motivo' => 'sin cobros vencidos'];
            }

            // 4. Teléfono válido
            $telefono = preg_replace('/\D/', '', (string) $empresa->telefono);
            if (strlen($telefono) < 10) {
                return ['estado' => 'saltado', 'motivo' => 'teléfono inválido'];
            }

            // 5. Calcular total con la MISMA lógica del modal/preview
            $desglose = $this->controller->calcularDesglosePago(
                $empresa->id,
                $suscripcion->id,
                $meses
            );

            $total = (float) $desglose['total'];

            if ($total <= 0) {
                return ['estado' => 'saltado', 'motivo' => 'total $0'];
            }

            // 6. Asegurar link de pago (crear si no existe)
            $link = $this->linkService->obtenerOCrear(
                $empresa->id,
                $empresa->id_usuario_owner
            );

            // 7. Datos de la plantilla WhatsApp
            $nombreCliente = $empresa->razon_social
                ?: trim("{$empresa->primer_nombre} {$empresa->otros_nombres} {$empresa->primer_apellido} {$empresa->segundo_apellido}");

            $whatsappData = [
                "1" => $nombreCliente ?: 'Cliente',
                "2" => $this->numeroFactura($empresa, $hoy),
                "3" => number_format($total, 2, ',', '.'),
                "4" => $hoy->format('Y-m'),
                "5" => "PORTAFOLIO ERP SAS",
                "6" => "pagar?token={$link->token}"
            ];

            // 8. Dry-run → no envía
            if ($dryRun) {
                return ['estado' => 'enviado', 'total' => $total];
            }

            // 9. Enviar WhatsApp
            $sender = new SendEcoWhatsApp(
                "57" . $telefono,
                // "573145876923",
                $whatsappData,
                [],
                'facturacion_portafolioerp',
                "facturacion_portafolioerp"
            );

            // 🔑 El botón de la plantilla apunta a https://app.portafolioerp.com/{{6}}
            //    Aquí pasamos solo el token para que arme la URL.
            // if (method_exists($sender, 'setBotonUrl')) {
            //     $sender->setBotonUrl($link->token);
            // }

            $sender->setToken(config('services.eco.email_token'));
            $response = $sender->send();

            Log::info('Cobro suscripción enviado', [
                'id_empresa' => $empresa->id,
                'telefono'   => $telefono,
                'total'      => $total,
                'meses'      => $meses,
                'link'       => $link->url_publica,
                'response'   => $response,
            ]);

            return ['estado' => 'enviado', 'total' => $total];

        } catch (\Throwable $e) {
            Log::error('Error cobrando suscripción', [
                'id_empresa' => $empresa->id,
                'error'      => $e->getMessage(),
                'line'       => $e->getLine(),
                'file'       => $e->getFile(),
            ]);

            return ['estado' => 'error', 'motivo' => $e->getMessage()];
        }
    }

    /**
     * Número de factura simple. Cambia esto por tu consecutivo real cuando lo tengas.
     */
    protected function numeroFactura(Empresa $empresa, Carbon $hoy): string
    {
        return $hoy->format('Ymd') . str_pad((string) $empresa->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Acepta "55", "55,60,72" o múltiples --empresa=XX
     */
    protected function parsearIds(array $raw): array
    {
        $ids = [];

        foreach ($raw as $valor) {
            foreach (explode(',', (string) $valor) as $id) {
                $id = (int) trim($id);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }

        return array_values(array_unique($ids));
    }
}