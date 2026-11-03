<?php

namespace App\Http\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Models\Empresas\Empresa;
use App\Models\Empresas\PagosSuscripcion;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacProductos;

class VentaSuscripcionService
{
    protected VentaServices $ventaServices;

    public function __construct(VentaServices $ventaServices)
    {
        $this->ventaServices = $ventaServices;
    }

    /**
     * Genera la venta en la BD del emisor a partir de un pago de suscripción.
     *
     * Es solo un wrapper: arma el contexto y delega a VentaServices::create().
     * Toda la lógica (contable, bodega, FE, email, consecutivo) vive en VentaServices.
     */
    public function generarVentaDesdePago(PagosSuscripcion $pago): ?FacVentas
    {
        try {
            // ── 1. Config del .env ──
            $tokenDbEmisor   = config('services.suscripcion_venta.token_db');
            $idUsuario       = config('services.suscripcion_venta.id_usuario');
            $idBodega        = config('services.suscripcion_venta.id_bodega');
            $idFormaPago     = config('services.suscripcion_venta.id_forma_pago');
            $idResolucion    = config('services.suscripcion_venta.id_resolucion');
            $idVendedor      = config('services.suscripcion_venta.id_vendedor');
            $idFamilia       = config('services.suscripcion_venta.id_familia');

            if (!$tokenDbEmisor || !$idUsuario || !$idBodega || !$idFormaPago || !$idResolucion) {
                Log::warning('Venta suscripción: faltan variables .env', [
                    'token_db'      => $tokenDbEmisor,
                    'id_usuario'    => $idUsuario,
                    'id_bodega'     => $idBodega,
                    'id_forma_pago' => $idFormaPago,
                    'id_resolucion' => $idResolucion,
                ]);
                return null;
            }

            // ── 2. Empresa compradora ──
            $empresaCompradora = Empresa::where('id', $pago->id_empresa)->first();
            if (!$empresaCompradora) {
                Log::warning('Venta suscripción: empresa compradora no existe', ['id' => $pago->id_empresa]);
                return null;
            }

            // ── 3. Idempotencia ──
            $existente = FacVentas::where('documento_referencia', 'SUSC-' . $pago->id)->first();
            if ($existente) {
                Log::info('Venta suscripción ya existe', [
                    'pago_id'  => $pago->id,
                    'venta_id' => $existente->id,
                ]);
                return $existente;
            }

            // ── 4. Detalle del pago ──
            $detalle     = json_decode($pago->detalle_json, true) ?: [];
            $componentes = $detalle['componentes'] ?? [];
            $meses       = (int) ($detalle['meses'] ?? 1);
            $desglose    = $detalle['desglose'] ?? [];

            if (empty($componentes)) {
                Log::warning('Venta suscripción: pago sin componentes', ['pago_id' => $pago->id]);
                return null;
            }

            // ── 5. Componentes normalizados: bruto, descuento propio y neto mensual ──
            //     La columna empresa_componentes_suscripcions.descuento es POR UNIDAD.
            //     neto_mensual = (precio - descuento) * cantidad
            $comps   = [];
            $sumNeto = 0.0;
            foreach ($componentes as $item) {
                $nombre = $item['nombre'] ?? null;
                $precio = (float) ($item['precio'] ?? 0);
                if (!$nombre || $precio <= 0) continue;

                $cant     = max(1, (int) ($item['cantidad'] ?? 1));
                $bruto    = $precio * $cant;
                $descUnit = min(max((float) ($item['descuento'] ?? 0), 0), $precio);
                $desc     = $descUnit * $cant;
                $neto     = $bruto - $desc;

                $comps[]  = [
                    'nombre' => $nombre,
                    'cant'   => $cant,
                    'bruto'  => $bruto,
                    'desc'   => $desc,
                    'neto'   => $neto,
                ];
                $sumNeto += $neto;
            }

            if ($sumNeto <= 0) {
                Log::warning('Venta suscripción: base mensual en 0', ['pago_id' => $pago->id]);
                return null;
            }

            $montoActual = (float) ($desglose['monto_mes_actual'] ?? 0);   // proporcional mes en curso (ya neto)
            $montoExtra  = (float) ($desglose['monto_meses_extra'] ?? 0);  // meses completos (ya neto)
            $descVol     = (float) ($desglose['descuento'] ?? 0);          // descuento por meses (solo mes actual)
            $porcentaje  = (float) ($desglose['porcentaje'] ?? 0);

            if ($montoActual <= 0 && $montoExtra <= 0) {
                $montoExtra = (float) $pago->valor;                        // pagos viejos sin desglose
            }

            // Arma 1 línea por componente para un bloque de cobro.
            //   bruto = precio de lista del bloque
            //   desc  = descuento propio del componente + parte del descuento por meses
            //   total = bruto - desc
            $armar = function (float $monto, float $descBloque, string $concepto) use ($comps, $sumNeto) {
                $f = $monto / $sumNeto;   // fracción del mes cubierta por el bloque
                $lineas = [];

                foreach ($comps as $c) {
                    $bruto   = round($c['bruto'] * $f, 2);
                    $descVal = round($c['desc'] * $f + $descBloque * ($c['neto'] / $sumNeto), 2);
                    $descVal = min($descVal, $bruto);
                    $total   = round($bruto - $descVal, 2);
                    $pct     = $bruto > 0 ? round($descVal / $bruto * 100, 2) : 0;   // máx. 2 decimales

                    $lineas[] = [
                        'nombre_producto'      => $c['nombre'],
                        'cantidad'             => $c['cant'],
                        'costo'                => $bruto,          // BRUTO: VentaServices resta descuento_valor
                        'costo_total'          => 0,
                        'subtotal'             => $bruto,
                        'descuento_porcentaje' => $pct,
                        'descuento_valor'      => $descVal,
                        'iva_porcentaje'       => 0,
                        'iva_valor'            => 0,
                        'total'                => $total,
                        'concepto'             => $concepto,
                    ];
                }
                return $lineas;
            };

            $productos = [];

            if ($montoActual > 0) {
                $label = ($desglose['detalle_meses'][0]['label'] ?? 'Mes actual')
                       . ($porcentaje > 0 ? " (desc. {$porcentaje}%)" : '');
                $productos = array_merge($productos, $armar($montoActual, $descVol, $label));
            }

            if ($montoExtra > 0) {
                $productos = array_merge($productos, $armar($montoExtra, 0.0, max(1, $meses - 1) . ' mes(es) completo(s)'));
            }

            // ── Cuadre automático contra lo que realmente se pagó ──
            if ($productos) {
                $dif = round((float) $pago->valor - array_sum(array_column($productos, 'total')), 2);

                if ($dif != 0 && abs($dif) <= 5) {
                    $i = array_key_last($productos);
                    $productos[$i]['descuento_valor']      = round($productos[$i]['descuento_valor'] - $dif, 2);
                    $productos[$i]['total']                = round($productos[$i]['total'] + $dif, 2);
                    $productos[$i]['descuento_porcentaje'] = $productos[$i]['costo'] > 0
                        ? round($productos[$i]['descuento_valor'] / $productos[$i]['costo'] * 100, 2) : 0;
                } elseif (abs($dif) > 5) {
                    Log::warning('Venta suscripción: no cuadra con el pago', [
                        'pago_id' => $pago->id,
                        'pago'    => $pago->valor,
                        'lineas'  => $pago->valor - $dif,
                        'dif'     => $dif,
                    ]);
                }
            }

            // ── 6. Armar el contexto y llamar al servicio ──
            $contexto = [
                'id_usuario'           => $idUsuario,
                'id_empresa'           => $pago->id_empresa,
                'token_db'             => $tokenDbEmisor,

                'id_cliente'           => null,
                'empresa_compradora'   => $empresaCompradora,

                'id_bodega'            => $idBodega,
                'id_resolucion'        => $idResolucion,
                'id_vendedor'          => $idVendedor,
                'id_familia'           => $idFamilia,

                'productos'            => $productos,
                'pagos'                => [[
                    'id'    => $idFormaPago,
                    'valor' => (float) $pago->valor,
                ]],
                'propina'              => 0,
                'calcula_retencion'    => false,

                'fecha_manual'         => Carbon::now()->format('Y-m-d'),
                'documento_referencia' => 'SUSC-' . $pago->id,
                'observacion'          => 'Venta automática suscripción #' . $pago->id,

                'auto_crear_nit'       => true,
                'auto_crear_producto'  => true,
                'fe_async'             => false,
            ];

            $venta = $this->ventaServices->create($contexto);

            Log::info('Venta suscripción generada', [
                'pago_id'          => $pago->id,
                'venta_id'         => $venta->id,
                'empresa_pagadora' => $empresaCompradora->id,
                'token_db_emisor'  => $tokenDbEmisor,
            ]);

            return $venta;

        } catch (\Throwable $e) {
            Log::error('Error generando venta suscripción', [
                'pago_id' => $pago->id,
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);
            return null;
        }
    }

}