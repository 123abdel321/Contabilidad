<?php

namespace App\Services\AI\Skills;

use Exception;
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

class CrearVentaSkill extends Skill
{
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

    public function name(): string
    {
        return 'crear_venta';
    }

    public function description(): string
    {
        return 'Crea la venta en el ERP con los datos ya recolectados en el borrador. Requiere cliente, resolución, bodega, productos y pagos. Úsala solo cuando el usuario confirme.';
    }

    public function parameters(): array
    {
        return [
            'propina' => [
                'type' => 'number',
                'description' => 'Valor de propina opcional.',
                'required' => false,
            ],
            'observacion' => [
                'type' => 'string',
                'description' => 'Observación opcional de la venta.',
                'required' => false,
            ],
            'confirmado' => [
                'type' => 'boolean',
                'description' => 'Debe ser true para confirmar que el usuario autorizó crear la venta.',
                'required' => true,
            ],
        ];
    }

    public function run(array $args, array &$state): array
    {
        if (empty($args['confirmado'])) {
            return ['success' => false, 'message' => 'Falta confirmación del usuario para crear la venta.'];
        }

        // ---------- 0. Leer borrador ----------
        $idUser    = auth()->id();
        $idEmpresa = auth()->user()->id_empresa;

        if (!$idUser || !$idEmpresa) {
            return ['success' => false, 'message' => 'Falta contexto de usuario/empresa en el estado.'];
        }

        $idCliente        = $state['id_cliente']             ?? null;
        $idResolucion     = $state['id_resolucion_venta']    ?? null;
        $idBodega         = $state['id_bodega']              ?? null;
        $productos        = $state['productos']              ?? [];
        $pagos            = $state['pagos']                  ?? $state['formas_pago_ventas'] ?? [];
        $documentoRef     = $state['documento_referencia']   ?? null;
        $fechaManual      = $state['fecha_manual']           ?? now()->format('Y-m-d');
        $idVendedor       = $state['id_vendedor']            ?? null;
        $propina          = (float) ($args['propina'] ?? $state['propina'] ?? 0);
        $observacion      = $args['observacion'] ?? $state['observacion'] ?? null;

        // Validaciones mínimas
        $faltantes = [];
        if (!$idCliente)    $faltantes[] = 'cliente';
        if (!$idResolucion) $faltantes[] = 'resolución';
        if (!$idBodega)     $faltantes[] = 'bodega';
        if (empty($productos)) $faltantes[] = 'productos';
        if (empty($pagos))  $faltantes[] = 'pagos';

        if ($faltantes) {
            return ['success' => false, 'message' => 'Faltan datos: ' . implode(', ', $faltantes)];
        }

        // Documento referencia por defecto: usar el consecutivo que saldrá
        // ---------- 1. Resolución ----------
        $this->resolucion = FacResoluciones::whereId($idResolucion)->with('comprobante')->first();

        if (!$this->resolucion) {
            return ['success' => false, 'message' => 'Resolución no encontrada.'];
        }
        if (!$this->resolucion->is_valid) {
            return ['success' => false, 'message' => "La resolución {$this->resolucion->nombre_completo} está agotada."];
        }

        // ---------- 2. Cliente y bodega ----------
        $this->nit    = $this->findCliente($idCliente);
        $this->bodega = FacBodegas::whereId($idBodega)->first();

        if (!$this->nit)    return ['success' => false, 'message' => 'Cliente no encontrado.'];
        if (!$this->bodega) return ['success' => false, 'message' => 'Bodega no encontrada.'];

        // IVA incluido
        $ivaIncluidoVar    = VariablesEntorno::where('nombre', 'iva_incluido')->first();
        $this->ivaIncluido = (bool) ($ivaIncluidoVar->valor ?? false);

        // ---------- 3. Consecutivo ----------
        $consecutivo = $this->getNextConsecutive(
            $this->resolucion->comprobante->id,
            $fechaManual
        );

        if (!$documentoRef) {
            $documentoRef = $this->resolucion->prefijo . $consecutivo;
        }

        $this->ventaServices = app(VentaServices::class);

        DB::connection('sam')->beginTransaction();

        try {
            // ---------- 4. Cabecera ----------
            $venta = $this->createFacturaVenta([
                'id_cliente'            => $idCliente,
                'id_resolucion'         => $idResolucion,
                'id_bodega'             => $idBodega,
                'id_comprobante'        => $this->resolucion->comprobante->id,
                'consecutivo'           => $consecutivo,
                'fecha_manual'          => $fechaManual,
                'documento_referencia'  => $documentoRef,
                'id_vendedor'           => $idVendedor,
                'observacion'           => $observacion,
                'propina'               => $propina,
                'productos'             => $productos,
                'pagos'                 => $pagos,
            ], $idUser);

            // ---------- 5. Documento general ----------
            $documentoGeneral = new Documento(
                $this->resolucion->comprobante->id,
                $venta,
                $fechaManual,
                $consecutivo,
                false,
                true
            );

            // ---------- 6. Detalles, contable y bodega ----------
            foreach ($productos as $productoRaw) {
                $producto   = (object) $productoRaw;
                $productoDb = $this->findProducto($producto->id_producto);

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
                    $ivaIncluidoCalc = round($subTotal * ($producto->iva_porcentaje / ($producto->iva_porcentaje + 100)), 2);
                    $subTotal       -= $ivaIncluidoCalc;
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

            // ---------- 7. Rete fuente ----------
            if ($this->totalesFactura['total_rete_fuente']) {
                $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();

                if (
                    $cuentaRetencion &&
                    ($cuentaRetencion->naturaleza_ventas == PlanCuentas::DEBITO ||
                     $cuentaRetencion->naturaleza_ventas == PlanCuentas::CREDITO)
                ) {
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
                } else {
                    throw new Exception('La cuenta de retención no tiene naturaleza en ventas.');
                }
            }

            // ---------- 8. Propina ----------
            if ($this->totalesFactura['propina']) {
                $cuentaPropinaCod = VariablesEntorno::where('nombre', 'cuenta_propina')->first()->valor ?? null;
                $cuentaPropina    = $cuentaPropinaCod ? PlanCuentas::whereCuenta($cuentaPropinaCod)->first() : null;

                if (
                    $cuentaPropina &&
                    ($cuentaPropina->naturaleza_ventas == PlanCuentas::DEBITO ||
                     $cuentaPropina->naturaleza_ventas == PlanCuentas::CREDITO)
                ) {
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
                } else {
                    throw new Exception('La cuenta de propina no tiene naturaleza en ventas.');
                }
            }

            // ---------- 9. Pagos ----------
            $saldoPendiente = $this->totalesFactura['total_factura'];

            foreach ($pagos as $pagoRaw) {
                $pagoItem  = (object) $pagoRaw;
                $formaPago = $this->findFormaPago($pagoItem->id);

                if (!$formaPago) {
                    throw new Exception("Forma de pago {$pagoItem->id} no encontrada.");
                }

                $pagoValor = $pagoItem->valor;
                if ($formaPago->tipoFormaPago && $formaPago->tipoFormaPago->codigo == FacTipoFormasPago::EFECTIVO) {
                    $pagoValor = $pagoItem->valor - $this->totalesPagos['total_cambio'];
                }

                $saldoPendiente -= $pagoValor;

                $anticipos = $this->isAnticiposDocumentoRefe($formaPago, $venta->id_nit, $fechaManual);

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

            // ---------- 10. Guardar documento ----------
            if (!$documentoGeneral->save()) {
                throw new Exception(json_encode($documentoGeneral->getErrors()));
            }

            // ---------- 11. Consecutivo ----------
            $this->updateConsecutivo($this->resolucion->comprobante->id, $consecutivo);

            DB::connection('sam')->commit();

            // Guardar resultado en el estado
            $state['venta_creada'] = [
                'id'          => $venta->id,
                'consecutivo' => $venta->consecutivo,
                'referencia'  => $venta->documento_referencia,
                'total'       => $venta->total_factura,
            ];

            return [
                'success' => true,
                'venta'   => $state['venta_creada'],
                'message' => "Venta {$venta->documento_referencia} creada con éxito.",
            ];

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ============================================================
    // Helpers (idénticos a tu controlador, adaptados sin request)
    // ============================================================

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

            $iva              = 0;
            $costo            = $producto->costo;
            $totalPorCantidad = $producto->cantidad * $costo;
            $cuentaIva        = $productoDb->familia->cuenta_venta_iva;

            if ($cuentaIva && $cuentaIva->impuesto) {
                $impuesto = $cuentaIva->impuesto;

                if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente']) {
                    $this->totalesFactura['porcentaje_iva'] = floatval($impuesto->porcentaje);
                    $this->totalesFactura['id_cuenta_iva']  = $cuentaIva->id;
                }

                if ($this->ivaIncluido) {
                    $subTotal            = $totalPorCantidad - $producto->descuento_valor;
                    $porcentajeIva       = $this->totalesFactura['porcentaje_iva'];
                    $iva                 = round($subTotal * ($porcentajeIva / (100 + $porcentajeIva)), 2);
                    $valor_linea_sin_iva = round($subTotal - $iva, 2);
                    $valor_linea_con_iva = round($subTotal, 2);
                } else {
                    $iva                 = round(($totalPorCantidad - $producto->descuento_valor) * ($this->totalesFactura['porcentaje_iva'] / 100), 2);
                    $valor_linea_sin_iva = round(($producto->cantidad * $costo) - $producto->descuento_valor, 2);
                    $valor_linea_con_iva = $valor_linea_sin_iva + $iva;
                }
            } else {
                $valor_linea_sin_iva = round(($producto->cantidad * $costo) - $producto->descuento_valor, 2);
                $valor_linea_con_iva = $valor_linea_sin_iva;
            }

            $this->totalesFactura['subtotal_sin_iva'] += $valor_linea_sin_iva;
            $this->totalesFactura['subtotal_con_iva'] += $valor_linea_con_iva;
            $this->totalesFactura['total_iva']        += $iva;
            $this->totalesFactura['total_descuento']  += $producto->descuento_valor;
        }

        $this->totalesFactura['subtotal']      = $this->totalesFactura['subtotal_sin_iva'];
        $this->totalesFactura['total_factura'] = $this->totalesFactura['subtotal_con_iva'];

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

            $formaPago = $this->findFormaPago($pago->id);

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
                    \Carbon\Carbon::parse($fechaVenta)->format('Y-m-d H:i:s'),
                    $formaPago->cuenta->id
                ))->anticiposDiscriminados()->get()->all();
            }
        }

        return [];
    }
}