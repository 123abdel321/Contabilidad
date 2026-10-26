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

            // ── 3. Idempotencia (por si acaso el servicio ya la tiene) ──
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

            if (empty($componentes)) {
                Log::warning('Venta suscripción: pago sin componentes', ['pago_id' => $pago->id]);
                return null;
            }

            // ── 5. Armar productos para el contexto ──
            //     Como auto_crear_producto = true, VentaServices se encarga de:
            //     buscar por nombre, y si no existe, crear como servicio.
            $productos = [];
            foreach ($componentes as $item) {
                $nombre = $item['nombre'] ?? null;
                $precio = (float) ($item['precio'] ?? 0);
                if (!$nombre || $precio <= 0) continue;

                $cantidad = max(1, (int) ($item['cantidad'] ?? 1));

                $productos[] = [
                    'nombre_producto'      => $nombre,   // ← VentaServices busca/crea por este campo
                    'cantidad'             => $cantidad,
                    'costo'                => $precio,
                    'descuento_porcentaje' => 0,
                    'descuento_valor'      => 0,
                    'iva_porcentaje'       => 0,
                    'iva_valor'            => 0,
                    'total'                => $precio * $cantidad,
                    'concepto'             => $meses > 1 ? "Suscripción {$meses} mes(es)" : null,
                ];
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