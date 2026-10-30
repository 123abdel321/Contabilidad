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

            // ── 5. Armar productos ──
            //     El desglose trae:
            //       - monto_mes_actual  (proporcional días del mes en curso)
            //       - monto_meses_extra (meses completos)
            //       - descuento         (se aplica al producto del mes actual)
            //     Se arma 1 producto por componente por cada bloque (actual / extra),
            //     con el descuento YA aplicado al costo, para que el movimiento contable cuadre.

            $montoActual = (float) ($desglose['monto_mes_actual'] ?? 0);
            $montoExtra  = (float) ($desglose['monto_meses_extra'] ?? 0);
            $descuento   = (float) ($desglose['descuento'] ?? 0);
            $porcentaje  = (float) ($desglose['porcentaje'] ?? 0);
            $precioMensualTotal = (float) ($desglose['precio_mensual'] ?? 0);

            // Fallback si el pago es viejo (sin desglose)
            if ($montoActual <= 0 && $montoExtra <= 0) {
                $montoExtra = (float) $pago->valor;
            }

            // Si no vino precio_mensual, lo calculamos de los componentes
            if ($precioMensualTotal <= 0) {
                foreach ($componentes as $item) {
                    $precioMensualTotal += ((float) ($item['precio'] ?? 0)) * max(1, (int) ($item['cantidad'] ?? 1));
                }
            }

            $productos = [];

            // ── Producto 1: proporcional mes actual (con descuento aplicado) ──
            if ($montoActual > 0) {
                foreach ($componentes as $item) {
                    $nombre = $item['nombre'] ?? null;
                    $precio = (float) ($item['precio'] ?? 0);
                    if (!$nombre || $precio <= 0) continue;

                    $cantidad   = max(1, (int) ($item['cantidad'] ?? 1));
                    $proporcion = $precioMensualTotal > 0
                        ? (($precio * $cantidad) / $precioMensualTotal)
                        : 1;

                    $costoBruto      = round($montoActual * $proporcion, 2);
                    $descuentoComp   = round($descuento   * $proporcion, 2);
                    $costoNeto       = round($costoBruto - $descuentoComp, 2);

                    $productos[] = [
                        'nombre_producto'      => $nombre,
                        'cantidad'             => $cantidad,
                        'costo'                => $costoNeto,
                        'costo_total'          => 0,
                        'subtotal'             => $costoNeto,
                        'descuento_porcentaje' => 0,
                        'descuento_valor'      => 0,
                        'iva_porcentaje'       => 0,
                        'iva_valor'            => 0,
                        'total'                => $costoNeto,
                        'concepto'             => ($desglose['detalle_meses'][0]['label'] ?? 'Mes actual')
                                                    . ($porcentaje > 0 ? " (desc. {$porcentaje}%)" : ''),
                    ];
                }
            }

            // ── Producto 2: meses completos extras (sin descuento) ──
            if ($montoExtra > 0) {
                foreach ($componentes as $item) {
                    $nombre = $item['nombre'] ?? null;
                    $precio = (float) ($item['precio'] ?? 0);
                    if (!$nombre || $precio <= 0) continue;

                    $cantidad   = max(1, (int) ($item['cantidad'] ?? 1));
                    $proporcion = $precioMensualTotal > 0
                        ? (($precio * $cantidad) / $precioMensualTotal)
                        : 1;

                    $costoComponente = round($montoExtra * $proporcion, 2);

                    $productos[] = [
                        'nombre_producto'      => $nombre,
                        'cantidad'             => $cantidad,
                        'costo'                => $costoComponente,
                        'costo_total'          => 0,
                        'subtotal'             => $costoComponente,
                        'descuento_porcentaje' => 0,
                        'descuento_valor'      => 0,
                        'iva_porcentaje'       => 0,
                        'iva_valor'            => 0,
                        'total'                => $costoComponente,
                        'concepto'             => ($meses - 1) . ' mes(es) completo(s)',
                    ];
                }
            }

            if (empty($productos)) {
                Log::warning('Venta suscripción: productos vacíos', ['pago_id' => $pago->id]);
                return null;
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