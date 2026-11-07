<?php

namespace App\Console\Commands;

use App\Models\Empresas\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActualizarDigitosVerificacionNits extends Command
{
    /**
     * Uso:
     * php artisan nits:actualizar-dv --id-empresa=1
     * php artisan nits:actualizar-dv --id-empresa=1 --dry-run
     * php artisan nits:actualizar-dv --id-empresa=1 --id=55
     * php artisan nits:actualizar-dv --id-empresa=1 --id=55 --dry-run
     */
    protected $signature = 'nits:actualizar-dv
                            {--id-empresa= : ID de la empresa (obligatorio)}
                            {--id= : ID de un NIT específico}
                            {--dry-run : Simula sin actualizar la base de datos}';

    protected $description = 'Calcula y actualiza automáticamente el dígito de verificación de los NIT.';

    public function handle(): int
    {
        $idEmpresa = $this->option('id-empresa');
        $id        = $this->option('id');
        $dryRun    = (bool) $this->option('dry-run');

        // ── Validar id-empresa ─────────────────────────────────────────
        if ($idEmpresa === null || $idEmpresa === '') {
            $this->error('Debe indicar el ID de la empresa con --id-empresa=ID');
            return self::FAILURE;
        }

        if (!ctype_digit((string) $idEmpresa) || (int) $idEmpresa <= 0) {
            $this->error('El --id-empresa proporcionado no es válido.');
            return self::FAILURE;
        }

        // ── Buscar empresa para obtener el token_db ────────────────────
        $empresa = Empresa::where('id', (int) $idEmpresa)->first();

        if (!$empresa) {
            $this->error("No se encontró la empresa con ID {$idEmpresa}.");
            return self::FAILURE;
        }

        if (empty($empresa->token_db)) {
            $this->error("La empresa #{$idEmpresa} no tiene token_db configurado.");
            return self::FAILURE;
        }

        // ── Configurar dinámicamente la conexión sam ───────────────────
        Config::set('database.connections.sam.database', $empresa->token_db);

        // Limpiar la conexión para que Laravel la reconecte con el nuevo database
        DB::purge('sam');

        $this->info('══════════════════════════════════════════');
        $this->info('  ACTUALIZACIÓN DE DV DE NITS');
        $this->info('  Empresa:     ' . $empresa->nombre . " (#{$empresa->id})");
        $this->info('  Base de datos: ' . $empresa->token_db);
        $this->info('  Modo: ' . ($dryRun ? 'DRY-RUN (SIMULACIÓN)' : 'REAL'));
        $this->info('  ID NIT: ' . ($id ?: 'TODOS'));
        $this->info('══════════════════════════════════════════');

        $query = DB::connection('sam')
            ->table('nits')
            ->select('id', 'numero_documento', 'digito_verificacion')
            ->orderBy('id');

        if ($id !== null && $id !== '') {
            if (!ctype_digit((string) $id) || (int) $id <= 0) {
                $this->error('El ID de NIT proporcionado no es válido.');
                return self::FAILURE;
            }

            $query->where('id', (int) $id);
        }

        $actualizados = 0;
        $correctos    = 0;
        $invalidos    = 0;
        $errores      = 0;

        $total = (clone $query)->count();

        $this->info("NIT encontrados: {$total}");
        $this->newLine();

        if ($total === 0) {
            $this->warn('No se encontraron registros.');
            return self::SUCCESS;
        }

        $query->chunkById(500, function ($nits) use (
            &$actualizados,
            &$correctos,
            &$invalidos,
            &$errores,
            $dryRun
        ) {
            foreach ($nits as $nit) {
                try {
                    $numero = trim((string) $nit->numero_documento);

                    // Validar documento
                    if (
                        $numero === '' ||
                        !ctype_digit($numero) ||
                        strlen($numero) > 15
                    ) {
                        $invalidos++;

                        $this->warn(
                            "  ⚠️ #{$nit->id} Documento inválido: {$numero}"
                        );

                        continue;
                    }

                    // Calcular DV
                    $dv = $this->calcularDigitoVerificacion($numero);

                    // Si ya está correcto, no actualizar
                    if ((string) $nit->digito_verificacion === (string) $dv) {
                        $correctos++;
                        continue;
                    }

                    if (!$dryRun) {
                        DB::connection('sam')
                            ->table('nits')
                            ->where('id', $nit->id)
                            ->update([
                                'digito_verificacion' => $dv,
                                'updated_at'          => now(),
                            ]);
                    }

                    $actualizados++;

                    $this->line(
                        "  ✅ #{$nit->id} {$numero} → DV: {$dv}"
                    );

                } catch (\Throwable $e) {
                    $errores++;

                    Log::error('Error actualizando DV de NIT', [
                        'id_empresa' => $empresa->id ?? null,
                        'token_db'   => $empresa->token_db ?? null,
                        'id'         => $nit->id,
                        'error'      => $e->getMessage(),
                    ]);

                    $this->error(
                        "  ❌ #{$nit->id}: {$e->getMessage()}"
                    );
                }
            }
        }, 'id');

        $this->newLine();
        $this->info('══════════════════════════════════════════');
        $this->info('  RESULTADO DEL PROCESO');
        $this->info("  Empresa:      {$empresa->nombre} (#{$empresa->id})");
        $this->info("  BD:           {$empresa->token_db}");
        $this->info("  Encontrados:  {$total}");
        $this->info("  " . ($dryRun ? 'Por actualizar: ' : 'Actualizados:   ') . $actualizados);
        $this->info("  Ya correctos: {$correctos}");
        $this->info("  Inválidos:    {$invalidos}");
        $this->info("  Errores:      {$errores}");
        $this->info('══════════════════════════════════════════');

        return $errores > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Calcula el dígito de verificación colombiano (Módulo 11).
     */
    protected function calcularDigitoVerificacion(string $nit): int
    {
        $factores = [
            3, 7, 13, 17, 19,
            23, 29, 37, 41, 43,
            47, 53, 59, 67, 71
        ];

        $nitInvertido = strrev($nit);
        $suma = 0;

        for ($i = 0; $i < strlen($nitInvertido); $i++) {
            $suma += (int) $nitInvertido[$i] * $factores[$i];
        }

        $residuo = $suma % 11;

        return $residuo < 2 ? $residuo : 11 - $residuo;
    }
}