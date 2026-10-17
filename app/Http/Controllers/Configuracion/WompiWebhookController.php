<?php

namespace App\Http\Controllers\Configuracion;

use DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessProvisionedDatabase;
// MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\PagosSuscripcion;
use App\Models\Empresas\EmpresaSuscripcion;
use App\Models\Empresas\EmpresaComponentesSuscripcion;

class WompiWebhookController extends Controller
{
    /**
     * POST /api/wompi/webhook
     * Wompi nos notifica cambios de estado en transacciones.
     */
    public function handle(Request $request)
    {
        // 🔑 Loguear todo para debug
        \Log::info('[Wompi Webhook] Payload recibido', [
            'body' => $request->all(),
        ]);

        // ── 1) Validar firma ──
        if (!$this->validarFirma($request)) {
            \Log::warning('[Wompi Webhook] Firma inválida', $request->all());
            return response()->json(['success' => false, 'message' => 'Firma inválida'], 401);
        }

        try {
            $event    = $request->get('event');           // 'transaction.updated'
            $data     = $request->get('data') ?? [];
            $tx       = $data['transaction'] ?? [];

            if (!$tx || empty($tx['reference'])) {
                return response()->json(['success' => false, 'message' => 'Sin datos de transacción'], 422);
            }

            $reference   = $tx['reference'];
            $wompiStatus = $tx['status'] ?? 'UNKNOWN';
            $txId        = $tx['id'] ?? null;

            // ── 2) Buscar el pago por referencia (token_db) ──
            $pago = PagosSuscripcion::where('token_db', $reference)->first();

            // Si ya se actualizó el token_db a la tx id, buscar por ahí también
            if (!$pago && $txId) {
                $pago = PagosSuscripcion::where('token_db', $txId)->first();
            }

            if (!$pago) {
                \Log::warning('[Wompi Webhook] Pago no encontrado para reference ' . $reference);
                return response()->json(['success' => false, 'message' => 'Pago no encontrado'], 404);
            }

            // ── 3) Si ya está procesado como aprobado, no hacer nada ──
            if ((int) $pago->estado === 1 && $wompiStatus === 'APPROVED') {
                return response()->json(['success' => true, 'message' => 'Ya procesado']);
            }

            $estadoPago = 2;
            if ($wompiStatus === 'APPROVED') $estadoPago = 1;
            elseif (in_array($wompiStatus, ['DECLINED', 'VOIDED', 'ERROR'])) $estadoPago = 0;

            // ── 4) Actualizar el pago ──
            $detalle = json_decode($pago->detalle_json, true) ?: [];
            $detalle['wompi_transaction_id'] = $txId;
            $detalle['wompi_status']         = $wompiStatus;
            $detalle['metodo_pago']          = $tx['payment_method_type'] ?? ($detalle['metodo_pago'] ?? 'Tarjeta');
            $detalle['webhook_recibido_at']  = Carbon::now()->toDateTimeString();

            $pago->detalle_json = json_encode($detalle);
            $pago->estado       = $estadoPago;
            if (!empty($txId)) $pago->token_db = $txId;
            $pago->save();

            // ── 5) Si fue aprobado, aplicar la lógica de suscripción ──
            if ($estadoPago === 1) {
                $this->procesarPagoAprobado($pago, $detalle, $tx);
            }

            return response()->json(['success' => true, 'message' => 'Webhook procesado']);

        } catch (Exception $e) {
            \Log::error('[Wompi Webhook] Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Valida la firma del webhook usando el events_secret.
     * Formato de Wompi:
     *   signature.properties = ["transaction.id","transaction.status","transaction.amount_in_cents"]
     *   signature.checksum   = SHA256( concat(valores de las props en orden) + timestamp + events_secret )
     */
    private function validarFirma(Request $request): bool
    {
        $secret = config('services.wompi.events_secret');
        if (!$secret) {
            \Log::warning('[Wompi Webhook] events_secret no configurado. Saltando validación.');
            return true; // si no hay secret, no se valida (útil en dev)
        }

        $signature = $request->get('signature');
        $timestamp = $request->get('timestamp');

        if (!$signature || !$timestamp || empty($signature['checksum']) || empty($signature['properties'])) {
            return false;
        }

        // Armar la cadena con los valores de las propiedades en el orden indicado
        $data       = $request->get('data');
        $cadena     = '';

        foreach ($signature['properties'] as $prop) {
            $cadena .= $this->obtenerValorPorRuta($data, $prop);
        }

        $cadena .= $timestamp;
        $cadena .= $secret;

        $checksumCalculado = hash('sha256', $cadena);

        return hash_equals($checksumCalculado, $signature['checksum']);
    }

    /**
     * Obtiene un valor dentro del array anidado usando notación "a.b.c".
     * Ej: obtenerValorPorRuta($data, 'transaction.id')
     */
    private function obtenerValorPorRuta($data, string $ruta)
    {
        $partes  = explode('.', $ruta);
        $valor   = $data;

        foreach ($partes as $p) {
            if (is_array($valor) && array_key_exists($p, $valor)) {
                $valor = $valor[$p];
            } else {
                return '';
            }
        }

        // Convertir bool y null a string consistente con Wompi
        if (is_bool($valor)) return $valor ? 'true' : 'false';
        if ($valor === null) return '';

        return (string) $valor;
    }

    /**
     * Lógica de negocio cuando el pago queda APROBADO.
     * Reutiliza la lógica existente de aplicarPagoAComponentes (ideal).
     * Aquí dejamos una versión simplificada que aplica fechas y crea componentes.
     */
    private function procesarPagoAprobado(PagosSuscripcion $pago, array $detalle, array $tx): void
    {
        DB::connection('clientes')->beginTransaction();

        try {
            $idEmpresa = $pago->id_empresa;
            $empresa   = Empresa::find($idEmpresa);

            if (!$empresa) {
                throw new Exception('Empresa no encontrada para el pago ' . $pago->id);
            }

            $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', '1')
                ->first();

            if (!$suscripcion) {
                throw new Exception('No hay suscripción activa para extender (empresa ' . $idEmpresa . ')');
            }

            $meses = (int) ($detalle['meses'] ?? 1);
            if ($meses < 1) $meses = 1;

            $hoy = Carbon::now()->startOfDay();

            // 🔑 Aplicar fechas a componentes existentes
            $actuales = EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                ->where('id_empresa_suscripcion', $suscripcion->id)
                ->has('componente')
                ->get();

            // Determinar estado global
            $fechaProximoCobro = $actuales->min('fecha_siguiente_cobro');

            $estado = 'nuevo';
            if ($fechaProximoCobro) {
                $estado = Carbon::parse($fechaProximoCobro)->startOfDay()->lt($hoy)
                    ? 'vencido'
                    : 'al_dia';
            }

            foreach ($actuales as $c) {
                if ($estado === 'vencido') {
                    $c->fecha_inicio_suscripcion = $hoy->copy()->startOfMonth()->format('Y-m-d');
                    $c->fecha_siguiente_cobro    = $hoy->copy()
                        ->startOfMonth()->addMonthsNoOverflow($meses)->subDay()->format('Y-m-d');
                } elseif ($estado === 'al_dia') {
                    $fechaActual = $c->fecha_siguiente_cobro
                        ? Carbon::parse($c->fecha_siguiente_cobro)->startOfDay()
                        : $hoy->copy();
                    $c->fecha_siguiente_cobro = $fechaActual->copy()
                        ->addMonthsNoOverflow($meses)->format('Y-m-d');
                } else {
                    $c->fecha_inicio_suscripcion = $hoy->format('Y-m-d');
                    $c->fecha_siguiente_cobro    = $hoy->copy()
                        ->endOfMonth()->addMonthsNoOverflow($meses - 1)->format('Y-m-d');
                }
                $c->save();
            }

            // 🔑 Crear componentes NUEVOS (si el pago los trae)
            foreach (($detalle['componentes'] ?? []) as $c) {
                if (empty($c['es_nuevo'])) continue;

                // Evitar duplicados
                $existe = EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                    ->where('id_empresa_suscripcion', $suscripcion->id)
                    ->where('id_componente', $c['id_componente'])
                    ->exists();

                if ($existe) continue;

                EmpresaComponentesSuscripcion::create([
                    'id_empresa'               => $idEmpresa,
                    'id_empresa_suscripcion'   => $suscripcion->id,
                    'id_componente'            => $c['id_componente'],
                    'cantidad'                 => 1,
                    'precio'                   => (float) $c['precio'],
                    'descuento'                => 0,
                    'fecha_inicio_suscripcion' => $hoy->format('Y-m-d'),
                    'fecha_siguiente_cobro'    => $hoy->copy()
                        ->endOfMonth()->addMonthsNoOverflow($meses - 1)->format('Y-m-d'),
                    'created_by'               => $empresa->id_usuario_owner ?: 0,
                    'updated_by'               => $empresa->id_usuario_owner ?: 0,
                ]);
            }

            // 🔑 Recalcular precio de la suscripción
            $total = 0;
            foreach (EmpresaComponentesSuscripcion::where('id_empresa', $idEmpresa)
                        ->where('id_empresa_suscripcion', $suscripcion->id)
                        ->has('componente')->get() as $c) {
                $cant = max(1, (int) ($c->cantidad ?? 1));
                $desc = (float) ($c->descuento ?? 0);
                $total += ((float) $c->precio - $desc) * $cant;
            }
            $suscripcion->precio = $total;
            $suscripcion->save();

            // 🔑 Activar empresa si está en instalación (estado 5)
            if ($empresa->estado == 5) {
                $owner = User::find($empresa->id_usuario_owner);
                if ($owner) {
                    $owner->id_empresa  = $empresa->id;
                    $owner->has_empresa = $empresa->token_db;
                    $owner->save();
                }

                DB::connection('clientes')->commit();

                // Disparar la provisión de la BD de la empresa
                ProcessProvisionedDatabase::dispatch(
                    $empresa->id,
                    'empresas_generales',
                    $owner->remember_token ?? null
                );

                \Log::info('[Wompi Webhook] Provisión despachada para empresa ' . $empresa->id);
            } else {
                DB::connection('clientes')->commit();
            }

            // Marcar link público como usado (si venía de un token público)
            $tokenLink = $detalle['token_link'] ?? null;
            if ($tokenLink) {
                $link = \App\Models\Empresas\SuscripcionPagoLink::where('token', $tokenLink)->first();
                if ($link) {
                    $link->marcarComoUsado();
                }
            }

        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            \Log::error('[Wompi Webhook] Error aplicando pago: ' . $e->getMessage());
            throw $e;
        }
    }
}