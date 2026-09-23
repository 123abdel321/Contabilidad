<?php

namespace App\Services\Ventas;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Helpers\Documento;
use App\Helpers\Extracto;
use App\Http\Services\VentaServices;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;

use App\Models\Sistema\Nits;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacVentaPagos;
use App\Models\Sistema\PlanCuentasTipo;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacTipoFormasPago;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class VentaCreatorService
{
    protected VentaServices $ventaServices;

    protected ?Nits $nit = null;
    protected ?FacBodegas $bodega = null;
    protected ?FacResoluciones $resolucion = null;
    protected bool $ivaIncluido = false;

    protected array $totalesFactura = [
        'tope_retencion'         => 0,
        'porcentaje_rete_fuente' => 0,
        'id_cuenta_rete_fuente'  => null,
        'porcentaje_iva'         => 0,
        'id_cuenta_iva'          => null,
        'propina'                => 0,
        'subtotal'               => 0,
        'subtotal_sin_iva'       => 0,
        'subtotal_con_iva'       => 0,
        'total_iva'              => 0,
        'total_rete_fuente'      => 0,
        'total_descuento'        => 0,
        'total_factura'          => 0,
    ];

    protected array $totalesPagos = [
        'total_efectivo'   => 0,
        'total_otrospagos' => 0,
        'total_cambio'     => 0,
    ];

    public function __construct(VentaServices $ventaServices)
    {
        $this->ventaServices = $ventaServices;
    }

    /**
     * Crea una venta completa SIN factura electrónica, SIN correos, SIN WhatsApp.
     *
     * @param array $data {
     *   id_cliente, id_resolucion, id_bodega, fecha_manual, documento_referencia,
     *   id_vendedor?, observacion?, propina?,
     *   productos: [{id_producto, cantidad, costo, descuento_porcentaje, descuento_valor, iva_porcentaje, iva_valor, total, concepto?}],
     *   pagos:     [{id, valor}]
     * }
     */
    public function crear(array $data, int $idUser, int $idEmpresa): array
    {
        // ---------- 0. Validaciones básicas ----------
        $errores = $this->validarEntrada($data);
        if (!empty($errores)) {
            return ['success' => false, 'errores' => $errores];
        }

        // ---------- 1. Resolución ----------
        $this->resolucion = FacResoluciones::whereId($data['id_resolucion'])
            ->with('comprobante')
            ->first();

        if (!$this->resolucion) {
            return ['success' => false, 'errores' => ['resolucion' => ['No encontrada.']]];
        }
        if (!$this->resolucion->is_valid) {
            return ['success' => false, 'errores' => [
                'resolucion' => ["La resolución {$this->resolucion->nombre_completo} está agotada."]
            ]];
        }

        // ---------- 2. Cliente y bodega ----------
        $this->nit    = $this->findCliente((int) $data['id_cliente']);
        $this->bodega = FacBodegas::whereId($data['id_bodega'])->first();

        if (!$this->nit)    return ['success' => false, 'errores' => ['cliente' => ['No encontrado.']]];
        if (!$this->bodega) return ['success' => false, 'errores' => ['bodega'  => ['No encontrada.']]];

        // ---------- 3. IVA incluido global ----------
        $ivaVar = VariablesEntorno::where('nombre', 'iva_incluido')->first();
        $this->ivaIncluido = (bool) ($ivaVar->valor ?? false);

        // ---------- 4. Consecutivo ----------
        $consecutivo = $this->getNextConsecutive(
            $this->resolucion->comprobante->id,
            $data['fecha_manual']
        );

        $documentoRef = $data['documento_referencia']
            ?: ($this->resolucion->prefijo . $consecutivo);

        // ---------- 5. Transacción ----------
        DB::connection('sam')->beginTransaction();

        try {
            // 5.1 Cabecera
            $venta = $this->createFacturaVenta([
                'id_cliente'           => $data['id_cliente'],
                'id_resolucion'        => $data['id_resolucion'],
                'id_bodega'            => $data['id_bodega'],
                'consecutivo'          => $consecutivo,
                'fecha_manual'         => $data['fecha_manual'],
                'documento_referencia' => $documentoRef,
                'id_vendedor'          => $data['id_vendedor']  ?? null,
                'observacion'          => $data['observacion']  ?? null,
                'propina'              => $data['propina']      ?? 0,
                'productos'            => $data['productos'],
                'pagos'                => $data['pagos'],
            ], $idUser);

            // 5.2 Documento general
            $documentoGeneral = new Documento(
                $this->resolucion->comprobante->id,
                $venta,
                $data['fecha_manual'],
                $consecutivo,
                false,
                true
            );

            // 5.3 Detalles + contable + bodega
            $this->procesarProductos(
                $venta,
                $data['productos'],
                $documentoGeneral,
                $idUser
            );

            // 5.4 Rete fuente
            $this->procesarRetencion($venta, $documentoGeneral, $idUser);

            // 5.5 Propina
            $this->procesarPropina($venta, $documentoGeneral, $idUser);

            // 5.6 Pagos
            $this->procesarPagos(
                $venta,
                $data['pagos'],
                $data['fecha_manual'],
                $documentoGeneral,
                $idUser
            );

            // 5.7 Guardar documento
            if (!$documentoGeneral->save()) {
                throw new Exception(json_encode($documentoGeneral->getErrors()));
            }

            // 5.8 Consecutivo
            $this->updateConsecutivo($this->resolucion->comprobante->id, $consecutivo);

            DB::connection('sam')->commit();

            return [
                'success' => true,
                'venta'   => [
                    'id'          => $venta->id,
                    'consecutivo' => $venta->consecutivo,
                    'referencia'  => $venta->documento_referencia,
                    'total'       => $venta->total_factura,
                ],
            ];

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return ['success' => false, 'errores' => ['venta' => [$e->getMessage()]]];
        }
    }

    // =========================================================
    // Sub-procesos
    // =========================================================

    protected function procesarProductos(
        FacVentas $venta,
        array $productos,
        Documento $documentoGeneral,
        int $idUser
    ): void {
        foreach ($productos as $productoRaw) {
            $producto   = (object) $productoRaw;
            $productoDb = $this->findProducto((int) $producto->id_producto);

            if (!$productoDb) {
                throw new Exception("Producto {$producto->id_producto} no encontrado.");
            }

            // Combo
            if ($productoDb->tipo_producto == 2) {
                $this->ventaServices->procesarCombo($producto, $venta, $documentoGeneral, $idUser);
                continue;
            }

            $producto->costo_total = $productoDb->precio_inicial * $producto->cantidad;

            $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
                ->where('id_producto', $producto->id_producto)
                ->first();

            if (
                $productoDb->familia->inventario &&
                $bodegaProducto &&
                $producto->cantidad > $bodegaProducto->cantidad
            ) {
                throw new Exception(
                    'La cantidad del producto ' . $productoDb->codigo . ' - ' . $productoDb->nombre .
                    ' supera la cantidad en bodega'
                );
            }

            $subTotal = (float) $producto->costo * $producto->cantidad;

            if ($this->ivaIncluido && !empty($this->totalesFactura['porcentaje_iva'])) {
                $ivaIncluidoCalc = round(
                    $subTotal * ($producto->iva_porcentaje / ($producto->iva_porcentaje + 100)),
                    2
                );
                $subTotal -= $ivaIncluidoCalc;
            }

            FacVentaDetalles::create([
                'id_venta'                  => $venta->id,
                'id_producto'               => $productoDb->id,
                'id_cuenta_venta'           => $productoDb->familia->id_cuenta_venta,
                'id_cuenta_venta_retencion' => $productoDb->familia->id_cuenta_venta_retencion,
                'id_cuenta_venta_iva'       => $productoDb->familia->id_cuenta_venta_iva,
                'id_cuenta_venta_descuento' => $productoDb->familia->id_cuenta_venta_descuento,
                'descripcion'               => $productoDb->codigo . ' - ' . $productoDb->nombre,
                'cantidad'                  => $producto->cantidad,
                'costo'                     => $producto->costo,
                'subtotal'                  => $subTotal,
                'descuento_porcentaje'      => $producto->descuento_porcentaje,
                'descuento_valor'           => $producto->descuento_valor,
                'iva_porcentaje'            => $producto->iva_porcentaje,
                'iva_valor'                 => $producto->iva_valor,
                'total'                     => $producto->total,
                'observacion'               => $producto->concepto ?? null,
                'created_by'                => $idUser,
                'updated_by'                => $idUser,
            ]);

            $this->ventaServices->movimientoContable(
                $venta,
                $productoDb,
                $documentoGeneral,
                (array) $producto
            );

            if ($productoDb->tipo_producto != 2) {
                if (!$bodegaProducto) {
                    $bodegaProducto = FacProductosBodegas::create([
                        'id_producto' => $producto->id_producto,
                        'id_bodega'   => $venta->id_bodega,
                        'cantidad'    => 0,
                        'created_by'  => $idUser,
                        'updated_by'  => $idUser,
                    ]);
                }

                $movimiento = new FacProductosBodegasMovimiento([
                    'id_producto'       => $producto->id_producto,
                    'id_bodega'         => $venta->id_bodega,
                    'cantidad_anterior' => $bodegaProducto->cantidad,
                    'cantidad'          => $producto->cantidad,
                    'tipo_tranferencia' => 2,
                    'inventario'        => $productoDb->familia->inventario ? 1 : 0,
                    'created_by'        => $idUser,
                    'updated_by'        => $idUser,
                ]);

                if ($bodegaProducto && $productoDb->familia->inventario) {
                    $bodegaProducto->updated_by = $idUser;
                    $bodegaProducto->cantidad  -= $producto->cantidad;
                    $bodegaProducto->save();
                }

                $movimiento->relation()->associate($venta);
                $venta->bodegas()->save($movimiento);
            }
        }
    }

    protected function procesarRetencion(FacVentas $venta, Documento $documentoGeneral, int $idUser): void
    {
        if (!$this->totalesFactura['total_rete_fuente']) return;

        $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();

        if (
            !$cuentaRetencion ||
            ($cuentaRetencion->naturaleza_ventas != PlanCuentas::DEBITO &&
             $cuentaRetencion->naturaleza_ventas != PlanCuentas::CREDITO)
        ) {
            throw new Exception('La cuenta de retención no tiene naturaleza en ventas.');
        }

        $doc = new DocumentosGeneral([
            'id_cuenta'            => $cuentaRetencion->id,
            'id_nit'               => $cuentaRetencion->exige_nit ? $venta->id_cliente : null,
            'id_centro_costos'     => $cuentaRetencion->exige_centro_costos ? $venta->id_centro_costos : null,
            'concepto'             => 'TOTAL: ' . ($cuentaRetencion->exige_concepto ? $this->nit->nombre_nit . ' - ' . $venta->documento_referencia : null),
            'documento_referencia' => $cuentaRetencion->exige_documento_referencia ? $venta->documento_referencia : null,
            'debito'               => $this->totalesFactura['total_rete_fuente'],
            'credito'              => $this->totalesFactura['total_rete_fuente'],
            'created_by'           => $idUser,
            'updated_by'           => $idUser,
        ]);
        $documentoGeneral->addRow($doc, $cuentaRetencion->naturaleza_ventas);
    }

    protected function procesarPropina(FacVentas $venta, Documento $documentoGeneral, int $idUser): void
    {
        if (!$this->totalesFactura['propina']) return;

        $cuentaPropinaCod = VariablesEntorno::where('nombre', 'cuenta_propina')->first()->valor ?? null;
        $cuentaPropina    = $cuentaPropinaCod ? PlanCuentas::whereCuenta($cuentaPropinaCod)->first() : null;

        if (
            !$cuentaPropina ||
            ($cuentaPropina->naturaleza_ventas != PlanCuentas::DEBITO &&
             $cuentaPropina->naturaleza_ventas != PlanCuentas::CREDITO)
        ) {
            throw new Exception('La cuenta de propina no tiene naturaleza en ventas.');
        }

        $doc = new DocumentosGeneral([
            'id_cuenta'            => $cuentaPropina->id,
            'id_nit'               => $cuentaPropina->exige_nit ? $venta->id_cliente : null,
            'id_centro_costos'     => $cuentaPropina->exige_centro_costos ? $venta->id_centro_costos : null,
            'concepto'             => 'TOTAL: ' . ($cuentaPropina->exige_concepto ? $this->nit->nombre_nit . ' - ' . $venta->documento_referencia : null),
            'documento_referencia' => $cuentaPropina->exige_documento_referencia ? $venta->documento_referencia : null,
            'debito'               => $this->totalesFactura['propina'],
            'credito'              => $this->totalesFactura['propina'],
            'created_by'           => $idUser,
            'updated_by'           => $idUser,
        ]);
        $documentoGeneral->addRow($doc, $cuentaPropina->naturaleza_ventas);
    }

    protected function procesarPagos(
        FacVentas $venta,
        array $pagos,
        string $fechaManual,
        Documento $documentoGeneral,
        int $idUser
    ): void {
        $saldoPendiente = $this->totalesFactura['total_factura'];

        foreach ($pagos as $pagoRaw) {
            $pagoItem  = (object) $pagoRaw;
            $formaPago = $this->findFormaPago((int) $pagoItem->id);

            if (!$formaPago) {
                throw new Exception("Forma de pago {$pagoItem->id} no encontrada.");
            }

            $pagoValor = $pagoItem->valor;
            if ($formaPago->tipoFormaPago && $formaPago->tipoFormaPago->codigo == FacTipoFormasPago::EFECTIVO) {
                $pagoValor = $pagoItem->valor - $this->totalesPagos['total_cambio'];
            }

            $saldoPendiente -= $pagoValor;

            $anticipos = $this->isAnticiposDocumentoRefe($formaPago, $venta->id_cliente, $fechaManual);

            if (count($anticipos)) {
                $pagoAnticipos = $pagoItem->valor;

                foreach ($anticipos as $anticipo) {
                    if (!$pagoAnticipos) break;

                    $disponible    = floatval($anticipo->saldo);
                    $anticipoUsado = $disponible >= $pagoAnticipos ? $pagoAnticipos : $disponible;

                    $pagoAnticipos -= $anticipoUsado;

                    $doc = $this->addFormaPago(
                        $anticipo->documento_referencia,
                        $formaPago,
                        $this->nit,
                        $pagoItem,
                        $venta,
                        $anticipoUsado,
                        $saldoPendiente,
                        $idUser
                    );
                    $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
                }
            } else {
                $doc = $this->addFormaPago(
                    $venta->documento_referencia,
                    $formaPago,
                    $this->nit,
                    $pagoItem,
                    $venta,
                    $pagoValor,
                    $saldoPendiente,
                    $idUser
                );
                $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
            }
        }
    }

    // =========================================================
    // Cabecera y cálculos
    // =========================================================

    protected function createFacturaVenta(array $data, int $idUser): FacVentas
    {
        $this->calcularTotales($data['productos']);

        $propina = $data['propina'] ?? 0;
        if ($propina) {
            $this->totalesFactura['propina']       = $propina;
            $this->totalesFactura['total_factura'] += $propina;
        }

        $this->calcularFormasPago($data['pagos']);

        return FacVentas::create([
            'id_cliente'                 => $data['id_cliente'],
            'id_resolucion'              => $data['id_resolucion'],
            'id_comprobante'             => $this->resolucion->comprobante->id,
            'id_bodega'                  => $data['id_bodega'],
            'id_centro_costos'           => $this->bodega->id_centro_costos,
            'id_vendedor'                => $data['id_vendedor'] ?? null,
            'fecha_manual'               => $data['fecha_manual'],
            'consecutivo'                => $data['consecutivo'],
            'documento_referencia'       => $data['documento_referencia'],
            'subtotal'                   => $this->totalesFactura['subtotal'],
            'total_iva'                  => $this->totalesFactura['total_iva'],
            'total_descuento'            => $this->totalesFactura['total_descuento'],
            'total_rete_fuente'          => $this->totalesFactura['total_rete_fuente'],
            'total_cambio'               => $this->totalesPagos['total_cambio'],
            'propina'                    => $this->totalesFactura['propina'],
            'porcentaje_rete_fuente'     => $this->totalesFactura['porcentaje_rete_fuente'],
            'codigo_tipo_documento_dian' => CodigoDocumentoDianTypes::VENTA_NACIONAL,
            'total_factura'              => $this->totalesFactura['total_factura'],
            'observacion'                => $data['observacion'] ?? null,
            'created_by'                 => $idUser,
            'updated_by'                 => $idUser,
        ]);
    }

    protected function calcularTotales(array $productos): void
    {
        $responsabilidades = $this->getResponsabilidades();

        $this->totalesFactura['subtotal_sin_iva'] = 0;
        $this->totalesFactura['subtotal_con_iva'] = 0;

        foreach ($productos as $productoRaw) {
            $producto = (object) $productoRaw;

            $productoDb = FacProductos::where('id', $producto->id_producto)
                ->with(
                    'familia.cuenta_venta',
                    'familia.cuenta_venta_retencion.impuesto',
                    'familia.cuenta_venta_iva.impuesto',
                    'familia.cuenta_venta_descuento'
                )
                ->first();

            if (!$productoDb) continue;

            // ---------------------------------------------------------
            // RETENCIÓN EN LA FUENTE (misma lógica que antes)
            // ---------------------------------------------------------
            $cuentaRetencion = $productoDb->familia->cuenta_venta_retencion;
            if ($cuentaRetencion && $cuentaRetencion->impuesto) {
                $impuesto   = $cuentaRetencion->impuesto;
                $baseActual = floatval($impuesto->base);

                if (
                    floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente'] &&
                    $baseActual > floatval($this->totalesFactura['tope_retencion'])
                ) {
                    $this->totalesFactura['porcentaje_rete_fuente'] = floatval($impuesto->porcentaje);
                    $this->totalesFactura['tope_retencion']         = floatval($impuesto->base);
                    $this->totalesFactura['id_cuenta_rete_fuente']  = $cuentaRetencion->id;
                }
            }

            // ---------------------------------------------------------
            // GUARDAR DATOS DE IVA PARA EL MOVIMIENTO CONTABLE
            // (aunque ya no recalculemos el IVA, el servicio lo necesita)
            // ---------------------------------------------------------
            $cuentaIva = $productoDb->familia->cuenta_venta_iva;
            if ($cuentaIva && $cuentaIva->impuesto) {
                if (floatval($cuentaIva->impuesto->porcentaje) > $this->totalesFactura['porcentaje_iva']) {
                    $this->totalesFactura['porcentaje_iva'] = floatval($cuentaIva->impuesto->porcentaje);
                    $this->totalesFactura['id_cuenta_iva']  = $cuentaIva->id;
                }
            }

            // ---------------------------------------------------------
            // USAR LOS VALORES YA CALCULADOS EN EL PRODUCTO
            // (vienen de BuscarProductoSkill o del front)
            // ---------------------------------------------------------
            $subtotal       = (float) ($producto->subtotal       ?? 0);
            $ivaValor       = (float) ($producto->iva_valor      ?? 0);
            $totalLinea     = (float) ($producto->total          ?? 0);
            $descuentoValor = (float) ($producto->descuento_valor ?? 0);

            // Si por alguna razón falta `subtotal`, lo reconstruimos
            // con lo mínimo que tengamos para no romper la venta.
            if ($subtotal <= 0 && $totalLinea > 0) {
                $subtotal = $this->ivaIncluido
                    ? round($totalLinea - $ivaValor, 2)
                    : round($totalLinea - $ivaValor, 2);
            }

            $this->totalesFactura['subtotal_sin_iva'] += $subtotal;
            $this->totalesFactura['subtotal_con_iva'] += $totalLinea;
            $this->totalesFactura['total_iva']        += $ivaValor;
            $this->totalesFactura['total_descuento']  += $descuentoValor;
        }

        $this->totalesFactura['subtotal']      = $this->totalesFactura['subtotal_sin_iva'];
        $this->totalesFactura['total_factura'] = $this->totalesFactura['subtotal_con_iva'];

        // ---------------------------------------------------------
        // RETENCIÓN EN LA FUENTE (a nivel factura)
        // ---------------------------------------------------------
        if (
            in_array('7', $responsabilidades) &&
            $this->totalesFactura['total_factura'] >= $this->totalesFactura['tope_retencion'] &&
            $this->totalesFactura['porcentaje_rete_fuente'] > 0
        ) {
            $base_retencion = $this->totalesFactura['subtotal_sin_iva'];
            $total_rete     = round($base_retencion * ($this->totalesFactura['porcentaje_rete_fuente'] / 100), 2);

            $this->totalesFactura['total_rete_fuente'] = $total_rete;
            $this->totalesFactura['total_factura']     = round($this->totalesFactura['total_factura'] - $total_rete, 2);
        } else {
            $this->totalesFactura['id_cuenta_rete_fuente'] = null;
            $this->totalesFactura['total_rete_fuente']     = 0;
        }
    }

    protected function calcularFormasPago(array $pagos): void
    {
        $totalCambio = 0;
        $totalPagos  = 0;

        foreach ($pagos as $pagoRaw) {
            $pago = (object) $pagoRaw;
            $totalPagos += $pago->valor;

            $formaPago = $this->findFormaPago((int) $pago->id);

            if ($formaPago && $formaPago->tipoFormaPago && $formaPago->tipoFormaPago->codigo == FacTipoFormasPago::EFECTIVO) {
                $this->totalesPagos['total_efectivo'] += $pago->valor;
            } else {
                $this->totalesPagos['total_otrospagos'] += $pago->valor;
            }
        }

        if ($this->totalesFactura['total_factura'] < $totalPagos) {
            $totalCambio = $totalPagos - $this->totalesFactura['total_factura'];
        }

        $this->totalesPagos['total_cambio'] = $totalCambio;
    }

    // =========================================================
    // Helpers
    // =========================================================

    protected function validarEntrada(array $data): array
    {
        $errores = [];

        foreach (['id_cliente', 'id_resolucion', 'id_bodega', 'fecha_manual'] as $campo) {
            if (empty($data[$campo])) {
                $errores[$campo] = ["El campo {$campo} es obligatorio."];
            }
        }

        if (empty($data['productos']) || !is_array($data['productos'])) {
            $errores['productos'] = ['Debe incluir al menos un producto.'];
        }

        if (empty($data['pagos']) || !is_array($data['pagos'])) {
            $errores['pagos'] = ['Debe incluir al menos un pago.'];
        }

        return $errores;
    }

    protected function getResponsabilidades(): array
    {
        if ($this->nit && $this->nit->id_responsabilidades) {
            return explode(',', $this->nit->id_responsabilidades);
        }
        return [];
    }

    protected function findCliente(int $id_cliente): ?Nits
    {
        return Nits::whereId($id_cliente)
            ->select(
                '*',
                DB::raw("CASE
                    WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END AS nombre_nit"),
                'id_responsabilidades'
            )
            ->first();
    }

    protected function findProducto(int $id_producto): ?FacProductos
    {
        $producto = FacProductos::where('id', $id_producto)
            ->with(
                'familia.cuenta_venta',
                'familia.cuenta_venta_retencion.impuesto',
                'familia.cuenta_venta_iva.impuesto',
                'familia.cuenta_venta_descuento',
                'familia.cuenta_inventario',
                'familia.cuenta_costos',
            )
            ->first();

        if ($producto && $producto->utilizado_captura == 0) {
            $producto->utilizado_captura = 1;
            $producto->save();
        }

        return $producto;
    }

    protected function findFormaPago(int $id_forma_pago): ?FacFormasPago
    {
        return FacFormasPago::where('id', $id_forma_pago)
            ->with('cuenta', 'tipoFormaPago')
            ->first();
    }

    protected function addFormaPago(
        string $documentoReferencia,
        FacFormasPago $formaPago,
        Nits $nit,
        object $pagoItem,
        FacVentas $venta,
        float $valor,
        float $saldo,
        int $idUser
    ): DocumentosGeneral {
        FacVentaPagos::create([
            'id_venta'      => $venta->id,
            'id_forma_pago' => $pagoItem->id,
            'valor'         => $valor,
            'saldo'         => $saldo,
            'created_by'    => $idUser,
            'updated_by'    => $idUser,
        ]);

        return new DocumentosGeneral([
            'id_cuenta'            => $formaPago->cuenta->id,
            'id_nit'               => $formaPago->cuenta->exige_nit ? $nit->id : null,
            'id_centro_costos'     => $formaPago->cuenta->exige_centro_costos ? $venta->id_centro_costos : null,
            'concepto'             => $formaPago->cuenta->exige_concepto ? 'TOTAL: ' . $nit->nombre_nit . ' - ' . $venta->documento_referencia : null,
            'documento_referencia' => $formaPago->cuenta->exige_documento_referencia ? $documentoReferencia : null,
            'debito'               => $valor,
            'credito'              => $valor,
            'created_by'           => $idUser,
            'updated_by'           => $idUser,
        ]);
    }

    protected function isAnticiposDocumentoRefe(FacFormasPago $formaPago, int $idNit, string $fechaVenta): array
    {
        $tiposCuenta = $formaPago->cuenta->tipos_cuenta ?? [];

        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == PlanCuentasTipo::TIPO_CUENTA_ANTICIPO_CLIENTES_XP) {
                return (new Extracto(
                    $idNit,
                    null,
                    null,
                    Carbon::parse($fechaVenta)->format('Y-m-d H:i:s'),
                    $formaPago->cuenta->id
                ))->anticiposDiscriminados()->get()->all();
            }
        }

        return [];
    }

    /**
     * TODO: mover a un ConsecutivoService cuando puedas.
     * Por ahora llamamos al trait del controlador vía reflexión.
     */
    protected function getNextConsecutive(int $idComprobante, string $fecha): int
    {
        $trait = new class {
            use \App\Http\Controllers\Traits\BegConsecutiveTrait;
        };

        $ref = new \ReflectionMethod($trait, 'getNextConsecutive');
        $ref->setAccessible(true);
        return $ref->invoke($trait, $idComprobante, $fecha);
    }

    protected function updateConsecutivo(int $idComprobante, int $consecutivo): void
    {
        $trait = new class {
            use \App\Http\Controllers\Traits\BegConsecutiveTrait;
        };

        $ref = new \ReflectionMethod($trait, 'updateConsecutivo');
        $ref->setAccessible(true);
        $ref->invoke($trait, $idComprobante, $consecutivo);
    }
}