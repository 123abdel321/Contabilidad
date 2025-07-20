<?php

namespace App\Http\Controllers\Capturas;

use DB;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacFactura;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacVentaPagos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacFacturaDetalle;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class NotaCreditoController extends Controller
{
    use BegConsecutiveTrait;

    protected $bodega = null;
    protected $facturaVentas = null;
    protected $resolucion = null;
	protected $messages = null;
    protected $totalesPagos = [
        'total_efectivo' => 0,
        'total_otrospagos' => 0,
    ];
    protected $totalesFactura = [
        'tope_retencion' => 0,
        'porcentaje_rete_fuente' => 0,
        'id_cuenta_rete_fuente' => null,
        'subtotal' => 0,
        'total_iva' => 0,
        'total_rete_fuente' => 0,
        'total_descuento' => 0,
        'total_factura' => 0,
    ];
    protected $cuentasContables = [
        "cuenta_venta_devolucion" => ["valor" => "subtotal", "opuesto" => false],
		"cuenta_venta_descuento" => ["valor" => "descuento_valor", "opuesto" => true],
        "cuenta_venta_devolucion_iva" => ["valor" => "iva_valor", "opuesto" => false],
        "cuenta_inventario" => ["valor" => "subtotal", "opuesto" => true],
        "cuenta_costos" => ["valor" => "subtotal", "opuesto" => true],
    ];

    public function __construct(Request $request)
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}

    public function index (Request $request)
    {
        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();

        $data = [
            'iva_incluido' => $ivaIncluido ? $ivaIncluido->valor : ''
        ];

        return view('pages.capturas.nota_credito.nota_credito-view', $data);
    }

    public function create (Request $request)
    {
        $rules = [
            'id_factura' => 'required|exists:sam.fac_ventas,id',
            'id_resolucion' => 'required|exists:sam.fac_resoluciones,id',
            'fecha_manual' => 'required|date',
            'documento_referencia' => 'required|string',
            'productos' => 'array|required',
            'productos.*.id_producto' => [
                'required',
                'exists:sam.fac_productos,id',
                function ($attribute, $value, $fail) {
					$producto = FacProductos::whereId($value)
                        ->with('familia')
                        ->first();

                    if (!$producto) {
                        $fail("El producto id: ". $value. " , No existe");
                    } else if (!$producto->familia->id_cuenta_venta_devolucion) {
                        $fail("La familia (".$producto->familia->codigo." - ".$producto->familia->nombre.") no tiene cuenta de venta devolución configurada");
                    }

                    
				}
            ],
            'productos.*.cantidad' => 'required|numeric|min:0',
            'productos.*.total_devolucion' => 'required|numeric|min:1',
            'pagos' => 'array|required',
            'pagos.*.id' => 'required|exists:sam.fac_formas_pagos,id',
            'pagos.*.valor' => 'required|numeric|min:1',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }        

        $this->facturaVentas = FacFactura::find($request->get('id_factura'));
        $this->resolucion = FacResoluciones::whereId($request->get('id_resolucion'))
            ->with('comprobante')
            ->first();

        if (!$this->resolucion->isValid) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>["Resolución" => ["La resolución {$this->resolucion->nombre_completo} está agotada"]]
            ], 422);
        }

        if (!$this->resolucion->isActive) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>["Resolución" => ["La resolución {$this->resolucion->nombre_completo} está vencida"]]
            ], 422);
        }

        $consecutivo = $this->getNextConsecutive($this->resolucion->comprobante->id, $request->get('fecha_manual'));
        
        $request->request->add([
            'id_comprobante' => $this->resolucion->comprobante->id,
            'consecutivo' => $consecutivo
        ]);

        try {
            DB::connection('sam')->beginTransaction();
            //CREAR NOTA CREDITO
            $notaCredito = $this->createNotaCredito($request);

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE VENTAS
            $documentoGeneral = new Documento(
                $this->resolucion->comprobante->id,
                $notaCredito,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );
            
            $count = 0;
            foreach ($request->get('productos') as $producto) {
                $producto = (object)$producto;
                $count++;
                $nit = $this->findCliente($notaCredito->id_cliente);
                $productoDb = $this->findProducto($producto->id_producto);
                $detalleProducto = FacFacturaDetalle::find($producto->id_factura_detalle);

                //AGREGAR MOVIMIENTO BODEGA
                if ($producto->cantidad && $productoDb->familia->inventario) {
                    $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
                        ->where('id_producto', $producto->id_producto)
                        ->first();

                    if (!$bodegaProducto) {
                        $bodegaProducto = FacProductosBodegas::create([
                            'id_producto' => $producto->id_producto,
                            'id_bodega' => $notaCredito->id_bodega,
                            'cantidad' => 0,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                    }

                    $movimiento = new FacProductosBodegasMovimiento([
                        'id_producto' => $producto->id_producto,
                        'id_bodega' => $notaCredito->id_bodega,
                        'cantidad_anterior' => $bodegaProducto->cantidad,
                        'cantidad' => $producto->cantidad,
                        'tipo_tranferencia' => 1,
                        'inventario' => $productoDb->familia->inventario ? 1 : 0,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    if ($bodegaProducto) {
                        $bodegaProducto->updated_by = request()->user()->id;
                        $bodegaProducto->cantidad+= $producto->cantidad;
                        $bodegaProducto->save();
                    }
                    $movimiento->relation()->associate($notaCredito);
                    $notaCredito->bodegas()->save($movimiento);

                    //AGREGAR INVENTARIO
                    if ($productoDb->precio_inicial) {
                        $cuentaCosto = $productoDb->familia->cuenta_costos;
                        $cuentaOpuestoCosto = PlanCuentas::CREDITO == $cuentaCosto->naturaleza_ventas ? PlanCuentas::DEBITO : PlanCuentas::CREDITO;

                        $docCosto = new DocumentosGeneral([
                            "id_cuenta" => $cuentaCosto->id,
                            "id_nit" => $cuentaCosto->exige_nit ? $notaCredito->id_cliente : null,
                            "id_centro_costos" => $cuentaCosto->exige_centro_costos ? $notaCredito->id_centro_costos : null,
                            "concepto" => $cuentaCosto->exige_concepto ? 'NOTA CREDITO: '.$nit->nombre_nit.' - '.$notaCredito->documento_referencia : null,
                            "documento_referencia" => $cuentaCosto->exige_documento_referencia ? $notaCredito->documento_referencia : null,
                            "debito" => $productoDb->precio_inicial * $producto->cantidad,
                            "credito" => $productoDb->precio_inicial * $producto->cantidad,
                            "created_by" => request()->user()->id,
                            "updated_by" => request()->user()->id
                        ]);
                        $documentoGeneral->addRow($docCosto, $cuentaOpuestoCosto);
                    }
                }

                $totalesProducto = $this->calcularTotalesProducto($producto);
                //CREAR NOTA CREDITO DETALLE
                FacVentaDetalles::create([
                    'id_venta' => $notaCredito->id,
                    'id_venta_detalle' => $detalleProducto->id,
                    'id_producto' => $productoDb->id,
                    'id_cuenta_venta' => $productoDb->familia->id_cuenta_venta_devolucion,
                    'id_cuenta_venta_retencion' => $productoDb->familia->id_cuenta_venta_retencion,
                    'id_cuenta_venta_iva' => $productoDb->familia->id_cuenta_venta_devolucion_iva,
                    'id_cuenta_venta_descuento' => $productoDb->familia->id_cuenta_venta_descuento,
                    'descripcion' => $productoDb->codigo.' - '.$productoDb->nombre,
                    'cantidad' => $producto->cantidad,
                    'costo' => $detalleProducto->costo,
                    'subtotal' => $totalesProducto->subtotal,
                    'descuento_porcentaje' => $detalleProducto->descuento_porcentaje,
                    'descuento_valor' => $totalesProducto->descuento,
                    'iva_porcentaje' => $detalleProducto->iva_porcentaje,
                    'iva_valor' => $totalesProducto->iva,
                    'total' => $producto->total_devolucion,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                //AGREGAR DEVOLUCION
                $cuentaDevolucion = $productoDb->familia->cuenta_venta_devolucion;
                $docDevolucion = new DocumentosGeneral([
                    "id_cuenta" => $cuentaDevolucion->id,
                    "id_nit" => $cuentaDevolucion->exige_nit ? $notaCredito->id_cliente : null,
                    "id_centro_costos" => $cuentaDevolucion->exige_centro_costos ? $notaCredito->id_centro_costos : null,
                    "concepto" => $cuentaDevolucion->exige_concepto ? 'NOTA: CREDITO '.$nit->nombre_nit.' - '.$notaCredito->documento_referencia : null,
                    "documento_referencia" => $cuentaDevolucion->exige_documento_referencia ? $notaCredito->documento_referencia : null,
                    "debito" => $totalesProducto->subtotal,
                    "credito" => $totalesProducto->subtotal,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                $documentoGeneral->addRow($docDevolucion, $cuentaDevolucion->naturaleza_ventas);

                //AGREGAR COSTO
                if ($totalesProducto->subtotal && $productoDb->familia->cuenta_compra) {
                    $cuentaCosto = $productoDb->familia->cuenta_compra;
                    $cuentaOpuestoCosto = PlanCuentas::CREDITO == $cuentaCosto->naturaleza_ventas ? PlanCuentas::DEBITO : PlanCuentas::CREDITO;

                    $docCosto = new DocumentosGeneral([
                        "id_cuenta" => $cuentaCosto->id,
                        "id_nit" => $cuentaCosto->exige_nit ? $notaCredito->id_cliente : null,
                        "id_centro_costos" => $cuentaCosto->exige_centro_costos ? $notaCredito->id_centro_costos : null,
                        "concepto" => $cuentaCosto->exige_concepto ? 'NOTA: CREDITO '.$nit->nombre_nit.' - '.$notaCredito->documento_referencia : null,
                        "documento_referencia" => $cuentaCosto->exige_documento_referencia ? $notaCredito->documento_referencia : null,
                        "debito" => $productoDb->precio_inicial,
                        "credito" => $productoDb->precio_inicial,
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($docCosto, $cuentaOpuestoCosto);
                }

                //AGREGAR DESCUENTO
                if ($totalesProducto->descuento && $productoDb->familia->cuenta_venta_descuento) {
                    $cuentaDescuento = $productoDb->familia->cuenta_venta_descuento;
                    $cuentaOpuestoDescuento = PlanCuentas::CREDITO == $cuentaDescuento->naturaleza_ventas ? PlanCuentas::DEBITO : PlanCuentas::CREDITO;

                    $docDevolucion = new DocumentosGeneral([
                        "id_cuenta" => $cuentaDescuento->id,
                        "id_nit" => $cuentaDescuento->exige_nit ? $notaCredito->id_cliente : null,
                        "id_centro_costos" => $cuentaDescuento->exige_centro_costos ? $notaCredito->id_centro_costos : null,
                        "concepto" => $cuentaDescuento->exige_concepto ? 'NOTA: CREDITO '.$nit->nombre_nit.' - '.$notaCredito->documento_referencia : null,
                        "documento_referencia" => $cuentaDescuento->exige_documento_referencia ? $notaCredito->documento_referencia : null,
                        "debito" => $totalesProducto->descuento,
                        "credito" => $totalesProducto->descuento,
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($docDevolucion, $cuentaOpuestoDescuento);
                }

                //AGREGAR IVA
                if ($totalesProducto->iva && $productoDb->familia->cuenta_venta_devolucion_iva) {
                    $cuentaIva = $productoDb->familia->cuenta_venta_devolucion_iva;

                    $docDevolucion = new DocumentosGeneral([
                        "id_cuenta" => $cuentaIva->id,
                        "id_nit" => $cuentaIva->exige_nit ? $notaCredito->id_cliente : null,
                        "id_centro_costos" => $cuentaIva->exige_centro_costos ? $notaCredito->id_centro_costos : null,
                        "concepto" => $cuentaIva->exige_concepto ? 'NOTA: CREDITO '.$nit->nombre_nit.' - '.$notaCredito->documento_referencia : null,
                        "documento_referencia" => $cuentaIva->exige_documento_referencia ? $notaCredito->documento_referencia : null,
                        "debito" => $totalesProducto->iva,
                        "credito" => $totalesProducto->iva,
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($docDevolucion, $cuentaIva->naturaleza_ventas);
                }

                //AGREGAR RETE FUENTE
                if ($this->totalesFactura['total_rete_fuente'] && $this->totalesFactura['id_cuenta_rete_fuente']) {
                    $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();
                    if ($cuentaRetencion) {
                        $cuentaOpuestoRetencion = PlanCuentas::CREDITO == $cuentaRetencion->naturaleza_ventas ? PlanCuentas::DEBITO : PlanCuentas::CREDITO;
    
                        $doc = new DocumentosGeneral([
                            "id_cuenta" => $cuentaRetencion->id,
                            "id_nit" => $cuentaRetencion->exige_nit ? $notaCredito->id_cliente : null,
                            "id_centro_costos" => $cuentaRetencion->exige_centro_costos ? $notaCredito->id_centro_costos : null,
                            "concepto" => $cuentaRetencion->exige_concepto ? 'TOTAL: '.$nit->nombre_nit.' - '.$notaCredito->documento_referencia : null,
                            "documento_referencia" => $cuentaRetencion->exige_documento_referencia ? $notaCredito->documento_referencia : null,
                            "debito" => $cuentaRetencion->naturaleza_ventas == PlanCuentas::DEBITO ? $this->totalesFactura['total_rete_fuente'] : 0,
                            "credito" => $cuentaRetencion->naturaleza_ventas == PlanCuentas::CREDITO ? $this->totalesFactura['total_rete_fuente'] : 0,
                            "created_by" => request()->user()->id,
                            "updated_by" => request()->user()->id
                        ]);
                        $documentoGeneral->addRow($doc, $cuentaOpuestoRetencion);
                    }
                }

                $totalProductos = $this->totalesFactura['total_factura'];

                //AGREGAR FORMAS DE PAGO
                foreach ($request->get('pagos') as $pago) {
                    $pago = (object)$pago;
                    $formaPago = $this->findFormaPago($pago->id);
                    $pagoValor = $pago->id == 1 ? $pago->valor - $this->totalesPagos['total_cambio'] : $pago->valor;
                    $cuentaOpuestoPago = PlanCuentas::CREDITO == $formaPago->cuenta->naturaleza_ventas ? PlanCuentas::DEBITO : PlanCuentas::CREDITO;

                    $totalProductos-= $pagoValor;

                    FacVentaPagos::create([
                        'id_venta' => $notaCredito->id,
                        'id_forma_pago' => $formaPago->id,
                        'valor' => $pagoValor,
                        'saldo' => $totalProductos,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    $doc = new DocumentosGeneral([
                        'id_cuenta' => $formaPago->cuenta->id,
                        'id_nit' => $formaPago->cuenta->exige_nit ? $notaCredito->id_cliente : null,
                        'id_centro_costos' => $formaPago->cuenta->exige_centro_costos ? $notaCredito->id_centro_costos : null,
                        'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL: '.$nit->nombre_nit.' - '.$notaCredito->documento_referencia : null,
                        'documento_referencia' => $formaPago->cuenta->exige_documento_referencia ? $notaCredito->documento_referencia : null,
                        'debito' => $pagoValor,
                        'credito' => $pagoValor,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($doc, $cuentaOpuestoPago);
                }
            }

            $this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));

            if (!$documentoGeneral->save()) {

                DB::connection('sam')->rollback();
                return response()->json([
                    'success'=>	false,
                    'data' => [],
                    'message'=> $documentoGeneral->getErrors()
                ], 422);
            }

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $documentoGeneral->getRows(),
                'impresion' => $this->resolucion->comprobante->imprimir_en_capturas ? $notaCredito->id : '',
                'message'=> 'Nota credito creado con exito!'
            ], 200);

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    private function createNotaCredito ($request)
    {
        $this->calcularTotales($request->get('productos'));
        $this->calcularFormasPago($request->get('pagos'));

        $this->bodega = FacBodegas::find($this->facturaVentas->id_bodega);

        return FacVentas::create([
            'id_cliente' => $this->facturaVentas->id_cliente,
            'id_factura' => $this->facturaVentas->id,
            'id_resolucion' => $request->get('id_resolucion'),
            'id_comprobante' => $this->resolucion->comprobante->id,
            'id_bodega' => $this->facturaVentas->id_bodega,
            'id_centro_costos' => $this->bodega->id_centro_costos,
            'fecha_manual' => $request->get('fecha_manual'),
            'consecutivo' => $request->get('documento_referencia'),
            'documento_referencia' => $request->get('documento_referencia'),
            'subtotal' => $this->totalesFactura['subtotal'],
            'total_iva' => $this->totalesFactura['total_iva'],
            'total_descuento' => $this->totalesFactura['total_descuento'],
            'total_rete_fuente' => $this->totalesFactura['total_rete_fuente'],
            'total_cambio' => $this->totalesPagos['total_cambio'],
            'porcentaje_rete_fuente' => $this->totalesFactura['porcentaje_rete_fuente'],
            'codigo_tipo_documento_dian' => CodigoDocumentoDianTypes::NOTA_CREDITO,
            'total_factura' => $this->totalesFactura['total_factura'],
            'observacion' => $request->get('observacion'),
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);
    }

    private function calcularTotalesProducto ($producto)
    {
        $subtotal = 0;
        $totalIva = 0;
        $totalDescuento = 0;
        $totalRetefuente = 0;

        $detalleProducto = FacFacturaDetalle::find($producto->id_factura_detalle);
        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();

        $subtotal = $producto->total_devolucion;
        $proporcion = $producto->total_devolucion / floatval($detalleProducto->total);

        if ($detalleProducto->iva_valor) {
            $totalIva = $detalleProducto->iva_valor * $proporcion;
            $subtotal-= $totalIva;
            // if ($ivaIncluido) {
            // }
        }

        if ($detalleProducto->descuento_valor) {
            $totalDescuento = $detalleProducto->descuento_valor * $proporcion;
            $subtotal-= $totalDescuento;
        }

        if ($this->facturaVentas->total_rete_fuente) {
            $proporcionReteFuente = $this->totalesFactura['total_factura'] / floatval($this->facturaVentas->total_factura);
            $totalRetefuente = $this->facturaVentas->total_rete_fuente * $proporcionReteFuente;
        }

        return (object)[
            "subtotal" => $subtotal,
            "iva" => $totalIva,
            "descuento" => $totalDescuento,
            "retefuente" => $totalRetefuente
        ];
    }

    private function calcularTotales ($productos)
    {
        foreach ($productos as $producto) {
            $producto = (object)$producto;
            $detalleProducto = FacFacturaDetalle::find($producto->id_factura_detalle);

            $productoDb = FacProductos::where('id', $producto->id_producto)
                ->with(
                    'familia.cuenta_venta_devolucion',
                    'familia.cuenta_venta_retencion.impuesto',
                    'familia.cuenta_venta_devolucion_iva.impuesto',
                    'familia.cuenta_venta_descuento'
                )
                ->first();

            $cuentaRetencion = $productoDb->familia->cuenta_venta_retencion;
            if ($cuentaRetencion && $cuentaRetencion->impuesto) {
                $impuesto = $cuentaRetencion->impuesto;
                if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente']) {
                    $this->totalesFactura['porcentaje_rete_fuente'] = floatval($impuesto->porcentaje);
                    $this->totalesFactura['tope_retencion'] = floatval($impuesto->base);
                    $this->totalesFactura['id_cuenta_rete_fuente'] = $cuentaRetencion->id;
                }
            }

            $proporcion = $producto->total_devolucion / floatval($detalleProducto->total);

            $this->totalesFactura['subtotal']+= $producto->total_devolucion;
            if ($detalleProducto->iva_valor) {
                $this->totalesFactura['total_iva']+= $detalleProducto->iva_valor * $proporcion;
                $this->totalesFactura['subtotal']-= $detalleProducto->iva_valor * $proporcion;
            }
            if ($detalleProducto->descuento_valor) {
                $this->totalesFactura['total_descuento']+= $detalleProducto->descuento_valor * $proporcion;
                $this->totalesFactura['subtotal']-= $detalleProducto->descuento_valor * $proporcion;
            }
            $this->totalesFactura['total_factura']+= $producto->total_devolucion;
        }
        if ($this->facturaVentas->total_rete_fuente) {
            $proporcionReteFuente = $this->totalesFactura['total_factura'] / floatval($this->facturaVentas->total_factura);
            $this->totalesFactura['total_rete_fuente'] = $this->facturaVentas->total_rete_fuente * $proporcionReteFuente;
        } else {
            $this->totalesFactura['id_cuenta_rete_fuente'] = null;
        }
    }

    private function calcularFormasPago($pagos)
    {
        $totalCambio = 0;
        $totalPagos = 0;
        foreach ($pagos as $pago) {
            $pago = (object)$pago;
            $totalPagos+= $pago->valor;
            if ($pago->id == 1) $this->totalesPagos['total_efectivo']+= $pago->valor;
            else $this->totalesPagos['total_otrospagos']+= $pago->valor;
        }
        if ($this->totalesFactura['total_factura'] < $totalPagos) {
            $totalCambio = $totalPagos - $this->totalesFactura['total_factura'];
        }
        
        $this->totalesPagos['total_cambio'] = $totalCambio;
    }

    public function detalleFactura (Request $request)
    {
        try {
            $facturaDetalles = FacFacturaDetalle::where('fac_venta_detalles.id_venta', $request->get('id'))
                ->with('producto')
                ->get();

            foreach ($facturaDetalles as $detalle) {
                $detalle->cantidad_devuelta = 0;
                $detalle->total_devuelto = 0;
                $devoluciones = FacFacturaDetalle::where('id_venta_detalle', $detalle->id);
                if ($devoluciones->count()) {
                    $devoluciones = $devoluciones->get();
                    foreach ($devoluciones as $devolucion) {
                        $detalle->cantidad_devuelta+= $devolucion->cantidad;
                        $detalle->total_devuelto+= $devolucion->total;
                    }
                }
            }

            return response()->json([
                "success" => true,
                "data" => $facturaDetalles,
                "message" => "Factura detalle consultada con exito!"
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    private function findCliente ($id_cliente)
    {
        return Nits::whereId($id_cliente)
            ->select(
                '*',
                DB::raw("CASE
                    WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END AS nombre_nit")
            )
            ->first();
    }

    private function findProducto ($id_producto)
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

        if ($producto->utilizado_captura == 0) {
            $producto->utilizado_captura = 1;
            $producto->save();
        }

        return $producto;
    }

    private function findFormaPago ($id_forma_pago)
    {
        return FacFormasPago::where('id', $id_forma_pago)
            ->with(
                'cuenta'
            )
            ->first();
    }

}