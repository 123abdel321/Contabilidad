<?php

namespace App\Http\Services;

//MODELS
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacProductosCombos;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;
//HELPERS
use App\Helpers\Documento;
use Exception;
use Illuminate\Support\Facades\DB;

class VentaServices
{
    protected $cuentasContables = [
        "cuenta_venta" => ["valor" => "subtotal"],
        "cuenta_venta_descuento" => ["valor" => "descuento_valor"],
        "cuenta_venta_iva" => ["valor" => "iva_valor"],
        "cuenta_inventario" => ["valor" => "costo_total"],
        "cuenta_costos" => ["valor" => "costo_total"],
    ];

    /**
     * Genera los movimientos contables para un producto (normal o servicio)
     */
    public function movimientoContable(
        FacVentas $venta,
        FacProductos $productoDb,
        Documento $documentoGeneral,
        array $producto
    ) {
        
        // Asegurar que el nit esté cargado
        if (!$venta->relationLoaded('cliente')) {
            $venta->load('cliente');
        }

        foreach ($this->cuentasContables as $cuentaKey => $cuenta) {
            $cuentaRecord = $productoDb->familia->{$cuentaKey};
            $keyTotalItem = $cuenta["valor"];

            //VALIDAR PRODUCTO INVENTARIO
            if ($productoDb->tipo_producto == 1 && $cuentaKey == 'cuenta_inventario') {
                continue;
            }

            if ($productoDb->tipo_producto == 1 && $cuentaKey == 'cuenta_costos') {
                continue;
            }

            //VALIDAR COSTO PRODUCTO
            if ($productoDb->precio_inicial <= 0 && $cuentaKey == 'cuenta_costos') {
                continue;
            }

            if (!$productoDb->familia->id_cuenta_inventario && $cuentaKey == 'cuenta_inventario') {
                continue;
            }

            if (!$productoDb->familia->id_cuenta_inventario && $cuentaKey == 'cuenta_costos') {
                continue;
            }

            if ($producto[$keyTotalItem] > 0) {

                if (!$cuentaRecord) {
                    return [
                        "success" => false,
                        "message" => [$productoDb->codigo.' - '.$productoDb->nombre => ['La cuenta '.str_replace('cuenta_venta_', '', $cuentaKey). ' no se encuentra configurada en la familia: '. $productoDb->familia->codigo. ' - '. $productoDb->familia->nombre]]
                    ];
                }

                $concepto = "VENTA: {$venta->cliente->nombre_completo} - {$venta->documento_referencia}";
                if (!empty($producto['concepto'])) {
                    $concepto.= " - {$producto['concepto']}";
                }

                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRecord->id,
                    "id_nit" => $cuentaRecord->exige_nit ? $venta->id_cliente : null,
                    "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $venta->id_centro_costos : null,
                    "concepto" => $cuentaRecord->exige_concepto ? $concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $venta->documento_referencia : null,
                    "debito" => $cuentaRecord->naturaleza_ventas == PlanCuentas::DEBITO ? $producto[$keyTotalItem] : 0,
                    "credito" => $cuentaRecord->naturaleza_ventas == PlanCuentas::CREDITO ? $producto[$keyTotalItem] : 0,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);

                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_ventas);
            }
        }
    }

    /**
     * Procesa un producto tipo combo: desglosa en sus productos hijos,
     * distribuye los valores (subtotal, IVA, descuento, total) proporcionalmente
     * al costo de inventario (precio_inicial * cantidad) de cada hijo.
     *
     * Retorna un array con los totales acumulados de los hijos.
     */
    public function procesarCombo(
        $productoCombo,        // objeto del combo recibido en el request
        FacVentas $venta,
        Documento $documentoGeneral,
        $userId
    ) {
        // Obtener los productos hijos del combo
        $hijos = FacProductosCombos::where('id_combo', $productoCombo->id_producto)->get();

        if ($hijos->isEmpty()) {
            throw new Exception("El combo {$productoCombo->id_producto} no tiene productos asociados.");
        }

        // Cargar los productos completos para obtener precios_inicial y familias
        $productosHijos = FacProductos::whereIn('id', $hijos->pluck('id_producto'))
            ->with([
                'familia.cuenta_venta',
                'familia.cuenta_venta_retencion.impuesto',
                'familia.cuenta_venta_iva.impuesto',
                'familia.cuenta_venta_descuento',
                'familia.cuenta_inventario',
                'familia.cuenta_costos'
            ])
            ->get()
            ->keyBy('id');

        // Calcular el costo total de inventario de cada hijo (precio_inicial * cantidad_hijo)
        $costosTotalesHijos = [];
        $sumaCostos = 0;

        foreach ($hijos as $hijo) {
            $productoHijo = $productosHijos->get($hijo->id_producto);
            if (!$productoHijo) {
                throw new Exception("Producto hijo {$hijo->id_producto} no encontrado.");
            }

            $cantidadHijo = $productoCombo->cantidad * $hijo->cantidad;
            $costoInventario = $productoHijo->precio_inicial * $cantidadHijo;
            $costosTotalesHijos[$hijo->id_producto] = [
                'producto' => $productoHijo,
                'cantidad' => $cantidadHijo,
                'costo_inventario' => $costoInventario,
            ];
            $sumaCostos += $costoInventario;
        }

        if ($sumaCostos == 0) {
            throw new Exception("La suma de costos de inventario del combo es cero. Verifique precios_inicial de los productos hijos.");
        }

        $totalesAcumulados = [
            'subtotal' => 0,
            'iva_valor' => 0,
            'descuento_valor' => 0,
            'total' => 0,
            'costo_total' => 0,
        ];

        // Distribuir los valores del combo entre los hijos
        foreach ($costosTotalesHijos as $idProducto => $data) {
            $productoHijo = $data['producto'];
            $cantidadHijo = $data['cantidad'];
            $costoInventarioHijo = $data['costo_inventario'];
            $porcentaje = $costoInventarioHijo / $sumaCostos;

            // Asignar valores proporcionales (redondeo a 2 decimales)
            $subtotalHijo = round($productoCombo->subtotal * $porcentaje, 2);
            $ivaHijo = round($productoCombo->iva_valor * $porcentaje, 2);
            $descuentoHijo = round($productoCombo->descuento_valor * $porcentaje, 2);
            $totalHijo = round($productoCombo->total * $porcentaje, 2);

            // Crear el array del producto virtual para pasar a movimientoContable
            $productoVirtual = [
                'id_producto' => $productoHijo->id,
                'cantidad' => $cantidadHijo,
                'costo' => $productoHijo->precio_inicial, // costo unitario de inventario
                'costo_total' => $costoInventarioHijo,
                'subtotal' => $subtotalHijo,
                'iva_valor' => $ivaHijo,
                'descuento_valor' => $descuentoHijo,
                'total' => $totalHijo,
                'concepto' => $productoCombo->concepto ?? null,
                'iva_porcentaje' => $productoCombo->iva_porcentaje ?? 0,
                'descuento_porcentaje' => $productoCombo->descuento_porcentaje ?? 0,
            ];

            // 1. Crear detalle de venta para el hijo
            FacVentaDetalles::create([
                'id_venta' => $venta->id,
                'id_producto' => $productoHijo->id,
                'id_cuenta_venta' => $productoHijo->familia->id_cuenta_venta ?? null,
                'id_cuenta_venta_retencion' => $productoHijo->familia->id_cuenta_venta_retencion ?? null,
                'id_cuenta_venta_iva' => $productoHijo->familia->id_cuenta_venta_iva ?? null,
                'id_cuenta_venta_descuento' => $productoHijo->familia->id_cuenta_venta_descuento ?? null,
                'descripcion' => $productoHijo->codigo . ' - ' . $productoHijo->nombre,
                'cantidad' => $cantidadHijo,
                'costo' => $productoHijo->precio_inicial,
                'subtotal' => $subtotalHijo,
                'descuento_porcentaje' => $productoVirtual['descuento_porcentaje'],
                'descuento_valor' => $descuentoHijo,
                'iva_porcentaje' => $productoVirtual['iva_porcentaje'],
                'iva_valor' => $ivaHijo,
                'total' => $totalHijo,
                'observacion' => $productoVirtual['concepto'],
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // 2. Movimiento contable para el hijo
            $this->movimientoContable(
                $venta,
                $productoHijo,
                $documentoGeneral,
                $productoVirtual
            );

            // 3. Movimiento de bodega para el hijo (similar a la lógica original del controlador)
            $bodegaProducto = FacProductosBodegas::where('id_bodega', $venta->id_bodega)
                ->where('id_producto', $productoHijo->id)
                ->first();

            if (!$bodegaProducto) {
                $bodegaProducto = FacProductosBodegas::create([
                    'id_producto' => $productoHijo->id,
                    'id_bodega' => $venta->id_bodega,
                    'cantidad' => 0,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            $movimiento = new FacProductosBodegasMovimiento([
                'id_producto' => $productoHijo->id,
                'id_bodega' => $venta->id_bodega,
                'cantidad_anterior' => $bodegaProducto->cantidad,
                'cantidad' => $cantidadHijo,
                'tipo_tranferencia' => 2,
                'inventario' => $productoHijo->familia->inventario ? 1 : 0,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            if ($bodegaProducto && $productoHijo->familia->inventario) {
                $bodegaProducto->updated_by = $userId;
                $bodegaProducto->cantidad -= $cantidadHijo;
                $bodegaProducto->save();
            }

            $movimiento->relation()->associate($venta);
            $venta->bodegas()->save($movimiento);

            // Acumular totales
            $totalesAcumulados['subtotal'] += $subtotalHijo;
            $totalesAcumulados['iva_valor'] += $ivaHijo;
            $totalesAcumulados['descuento_valor'] += $descuentoHijo;
            $totalesAcumulados['total'] += $totalHijo;
            $totalesAcumulados['costo_total'] += $costoInventarioHijo;
        }

        return $totalesAcumulados;
    }
}