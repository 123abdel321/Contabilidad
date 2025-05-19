<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Illuminate\Http\Request;
use App\Jobs\ProcessConsultarFE;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;  
use Illuminate\Support\Facades\Validator;
//HELPERS
use App\Helpers\Documento;
use App\Helpers\Printers\VentasPdf;
use App\Helpers\Printers\VentasInformeZ;
use App\Helpers\FacturaElectronica\VentaElectronicaSender;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//TRAITS
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
use App\Http\Controllers\Traits\BegFacturacionElectronica;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacVentaPagos;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Empresas\UsuarioPermisos;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class VentaController extends Controller
{
    use BegConsecutiveTrait;
    use BegDocumentHelpersTrait;
    use BegFacturacionElectronica;

    protected $bodega = null;
    protected $resolucion = null;
	protected $messages = null;
    protected $ventaData = [];
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
        "cuenta_venta" => ["valor" => "subtotal"],
		"cuenta_venta_descuento" => ["valor" => "descuento_valor"],
        "cuenta_venta_iva" => ["valor" => "iva_valor"],
        "cuenta_inventario" => ["valor" => "costo_total"],
        "cuenta_costos" => ["valor" => "costo_total"],
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
        $usuarioPermisos = UsuarioPermisos::where('id_user', request()->user()->id)
            ->where('id_empresa', request()->user()->id_empresa)
            ->first();

        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();
        $vendedorVentas = VariablesEntorno::where('nombre', 'vendedores_ventas')->first();

        $bodegas = explode(",", $usuarioPermisos->ids_bodegas_responsable);
        $resoluciones = explode(",", $usuarioPermisos->ids_resolucion_responsable);

        $data = [
            'cliente' => Nits::with('vendedor.nit')->where('numero_documento', 'LIKE', '22222222%')->first(),
            'bodegas' => FacBodegas::whereIn('id', $bodegas)->get(),
            'resolucion' => FacResoluciones::whereIn('id', $resoluciones)->get(),
            'iva_incluido' => $ivaIncluido ? $ivaIncluido->valor : '',
            'vendedores_ventas' => $vendedorVentas ? $vendedorVentas->valor : ''
        ];

        return view('pages.capturas.venta.venta-view', $data);
    }

    public function indexInforme ()
    {
        return view('pages.contabilidad.ventas.ventas-view');
    }

    public function create (Request $request)
    {
        $rules = [
            'id_cliente' => 'required|exists:sam.nits,id',
            'id_bodega' => 'required|exists:sam.fac_bodegas,id',
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

                    if (!$producto->id_familia) {
                        $fail("El producto (".$producto->codigo." - ".$producto->nombre.") no tiene familia venta configurada");
                    } else if (!$producto->familia->id_cuenta_venta) {
                        $fail("La familia (".$producto->familia->codigo." - ".$producto->familia->nombre.") no tiene cuenta venta configurada");
                    }
				}
            ],
            'productos.*.cantidad' => 'required|numeric|min:1',
            'productos.*.costo' => 'required|min:0',
            'productos.*.descuento_porcentaje' => 'required|numeric|min:0|max:100',
            'productos.*.descuento_valor' => 'required|numeric|min:0',
            'productos.*.iva_porcentaje' => 'required|numeric|min:0|max:100',
            'productos.*.iva_valor' => 'required|numeric|min:0',
            'productos.*.total' => 'required|numeric|min:0',
            'productos.*.concepto' => 'nullable',
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

        $empresa = Empresa::where('id', $request->user()->id_empresa)->first();

        try {
            DB::connection('sam')->beginTransaction();
            //CREAR FACTURA VENTA
            $venta = $this->createFacturaVenta($request);
            $nit = $this->findCliente($venta->id_nit);

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE VENTAS
            $documentoGeneral = new Documento(
                $this->resolucion->comprobante->id,
                $venta,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            //AGREGAR DETALLE DE PRODUCTOS
            foreach ($request->get('productos') as $producto) {
                $producto = (object)$producto;
                $nit = $this->findCliente($venta->id_cliente);
                $productoDb = $this->findProducto($producto->id_producto);
                $producto->costo_total = $productoDb->precio_inicial * $producto->cantidad;

                //AGREGAR MOVIMIENTO BODEGA
                $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
                    ->where('id_producto', $producto->id_producto)
                    ->first();
                    
                if (
                    $productoDb->familia->inventario &&
                    $bodegaProducto &&
                    $producto->cantidad > $bodegaProducto->cantidad &&
                    !request()->user()->can('venta negativa')
                ) {

                    DB::connection('sam')->rollback();
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message"=> ['Cantidad bodega' => ['La cantidad del producto '.$productoDb->codigo. ' - ' .$productoDb->nombre. ' supera la cantidad en bodega']]
                    ], 422);
                }

                //CREAR VENTA DETALLE
                FacVentaDetalles::create([
                    'id_venta' => $venta->id,
                    'id_producto' => $productoDb->id,
                    'id_cuenta_venta' => $productoDb->familia->id_cuenta_venta,
                    'id_cuenta_venta_retencion' => $productoDb->familia->id_cuenta_venta_retencion,
                    'id_cuenta_venta_iva' => $productoDb->familia->id_cuenta_venta_iva,
                    'id_cuenta_venta_descuento' => $productoDb->familia->id_cuenta_venta_descuento,
                    'descripcion' => $productoDb->codigo.' - '.$productoDb->nombre,
                    'cantidad' => $producto->cantidad,
                    'costo' => $producto->costo,
                    'subtotal' => $producto->costo * $producto->cantidad,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'descuento_valor' => $producto->descuento_valor,
                    'iva_porcentaje' => $producto->iva_porcentaje,
                    'iva_valor' => $producto->iva_valor,
                    'total' => $producto->total,
                    'observacion' => $producto->concepto,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                //AGREGAR MOVIMIENTO CONTABLE
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

                    if ($producto->{$keyTotalItem} > 0) {
                        
                        if(!$cuentaRecord) {
                            DB::connection('sam')->rollback();
                            return response()->json([
                                "success"=>false,
                                'data' => [],
                                "message"=> [$productoDb->codigo.' - '.$productoDb->nombre => ['La cuenta '.str_replace('cuenta_venta_', '', $cuentaKey). ' no se encuentra configurada en la familia: '. $productoDb->familia->codigo. ' - '. $productoDb->familia->nombre]]
                            ], 422);
                        }

                        $concepto = "VENTA: {$nit->nombre_nit} - {$nit->documento} - {$venta->documento_referencia}";
                        if ($producto->concepto) {
                            $concepto.= " - {$producto->concepto}";
                        }
        
                        $doc = new DocumentosGeneral([
                            "id_cuenta" => $cuentaRecord->id,
                            "id_nit" => $cuentaRecord->exige_nit ? $venta->id_cliente : null,
                            "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $venta->id_centro_costos : null,
                            "concepto" => $cuentaRecord->exige_concepto ? $concepto : null,
                            "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $venta->documento_referencia : null,
                            "debito" => $cuentaRecord->naturaleza_ventas == PlanCuentas::DEBITO ? $producto->{$keyTotalItem} : 0,
                            "credito" => $cuentaRecord->naturaleza_ventas == PlanCuentas::CREDITO ? $producto->{$keyTotalItem} : 0,
                            "created_by" => request()->user()->id,
                            "updated_by" => request()->user()->id
                        ]);

                        $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_ventas);
                    }
                }

                //AGREGAR MOVIMIENTO BODEGA
                $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
                    ->where('id_producto', $producto->id_producto)
                    ->first();

                if (!$bodegaProducto) {
                    $bodegaProducto = FacProductosBodegas::create([
                        'id_producto' => $producto->id_producto,
                        'id_bodega' => $venta->id_bodega,
                        'cantidad' => 0,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
                }

                $movimiento = new FacProductosBodegasMovimiento([
                    'id_producto' => $producto->id_producto,
                    'id_bodega' => $venta->id_bodega,
                    'cantidad_anterior' => $bodegaProducto->cantidad,
                    'cantidad' => $producto->cantidad,
                    'tipo_tranferencia' => 2,
                    'inventario' => $productoDb->familia->inventario ? 1 : 0,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                if ($bodegaProducto && $productoDb->familia->inventario) {
                    $bodegaProducto->updated_by = request()->user()->id;
                    $bodegaProducto->cantidad-= $producto->cantidad;
                    $bodegaProducto->save();
                }

                $movimiento->relation()->associate($venta);
                $venta->bodegas()->save($movimiento);
            }

            //AGREGAR RETE FUENTE
            if ($this->totalesFactura['total_rete_fuente']) {
                $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();

                if ($cuentaRetencion->naturaleza_ventas == PlanCuentas::DEBITO || $cuentaRetencion->naturaleza_ventas == PlanCuentas::CREDITO) {
                    $doc = new DocumentosGeneral([
                        "id_cuenta" => $cuentaRetencion->id,
                        "id_nit" => $cuentaRetencion->exige_nit ? $venta->id_cliente : null,
                        "id_centro_costos" => $cuentaRetencion->exige_centro_costos ? $venta->id_centro_costos : null,
                        "concepto" => 'TOTAL: '.$cuentaRetencion->exige_concepto ? $nit->nombre_nit.' - '.$venta->documento_referencia : null,
                        "documento_referencia" => $cuentaRetencion->exige_documento_referencia ? $venta->documento_referencia : null,
                        "debito" => $this->totalesFactura['total_rete_fuente'],
                        "credito" => $this->totalesFactura['total_rete_fuente'],
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($doc, $cuentaRetencion->naturaleza_ventas);
                } else {
                    DB::connection('sam')->rollback();
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message"=> ['Cuenta retención' => ['La cuenta '.$cuentaRetencion->cuenta. ' - ' .$cuentaRetencion->nombre. ' no tiene naturaleza en ventas']]
                    ], 422);
                }
            }

            $totalPagos = $this->totalesFactura['total_factura'];
            
            //AGREGAR FORMAS DE PAGO
            foreach ($request->get('pagos') as $pagoItem) {

                $pagoItem = (object)$pagoItem;
                $pagoValor = $pagoItem->id == 1 ? $pagoItem->valor - $this->totalesPagos['total_cambio'] : $pagoItem->valor;
                $totalPagos-= $pagoValor;
                $formaPago = $this->findFormaPago($pagoItem->id);
                $documentoReferenciaAnticipos = $this->isAnticiposDocumentoRefe($formaPago, $venta->id_nit);
                //CRUSAR ANTICIPOS
                if (count($documentoReferenciaAnticipos)) {

                    $pagoAnticipos = $pagoItem->valor;

                    foreach ($documentoReferenciaAnticipos as $anticipos) {

                        if (!$pagoAnticipos) {
                            break;
                        }

                        $anticipoUsado = 0;
                        $anticipoDisponible = floatval($anticipos->saldo);

                        if ($anticipoDisponible >= $pagoAnticipos) {
                            $anticipoUsado = $pagoAnticipos;
                        } else {
                            $anticipoUsado = $anticipoDisponible;
                        }

                        $pagoAnticipos-= $anticipoUsado;

                        $doc = $this->addFormaPago(
                            $anticipos->documento_referencia,
                            $formaPago,
                            $nit,
                            $pagoItem,
                            $venta,
                            $anticipoUsado,
                            $totalPagos
                        );
                        $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
                    }
                } else {
                    $doc = $this->addFormaPago(
                        null,
                        $formaPago,
                        $nit,
                        $pagoItem,
                        $venta,
                        $pagoItem->valor,
                        $totalPagos
                    );
                    $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
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

            $feSended = false;
            $hasCufe = false;

            //FACTURAR ELECTRONICAMENTE
            // if ($this->resolucion->tipo_resolucion == FacResoluciones::TIPO_FACTURA_ELECTRONICA) {
            //     $ventaElectronica = (new VentaElectronicaSender($venta))->send();

            //     if ($ventaElectronica["status"] >= 400) {
            //         if ($ventaElectronica["zip_key"]) {
            //             $venta->fe_zip_key = $ventaElectronica["zip_key"];
            //             $venta->save();
    
            //             if ($ventaElectronica["message_object"] == 'Batch en proceso de validación.') {
            //                 //JOB CONSULTAR FACTURA EN 1MN
            //                 info('Batch en proceso de validación.');
            //                 ProcessConsultarFE::dispatch($venta->id, $ventaElectronica["zip_key"], $request->user()->id, $empresa->id)->delay(now()->addSeconds(10));
            //             }
            //         }
            //     }

            //     if ($ventaElectronica['status'] == 200) {
            //         $feSended = $ventaElectronica['status'] == 200;
            //         $hasCufe = (isset($ventaElectronica['cufe']) && $ventaElectronica['cufe']);
    
            //         if($feSended || $hasCufe){
            //             $ventaElectronica['status'] = 200;
            //             $venta = $this->SetFeFields($venta, $ventaElectronica['cufe'], $empresa->nit);
            //             $venta->fe_zip_key = $ventaElectronica['zip_key'];
            //             $venta->save();
            //         }
            //     }
            // }

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => $documentoGeneral->getRows(),
				'impresion' => $this->resolucion->comprobante->imprimir_en_capturas ? $venta->id : '',
				'message'=> 'Venta creada con exito!'
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

    public function generate(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $searchValue = $search_arr['value']; // Search value

		$ventas = FacVentas::orderBy('id', 'DESC')
            ->skip($start)
            ->with(
                'resolucion',
                'bodega',
                'cliente',
                'comprobante',
                'detalles',
                'vendedor.nit',
                'pagos'
            )
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            )
            ->take($rowperpage);
        
        if ($request->get('id_cliente')) {
            $ventas->where('id_cliente', $request->get('id_cliente'));
        }

        if ($request->get('fecha_desde')) {
            $ventas->where('fecha_manual', '>=', $request->get('fecha_desde'));
        }

        if ($request->get('fecha_hasta')) {
            $ventas->where('fecha_manual', '<=', $request->get('fecha_hasta'));
        }

        if ($request->get('factura')) {
            $ventas->where('documento_referencia', 'LIKE', '%'.$request->get('factura').'%');
        }

        if ($request->get('id_resolucion')) {
            $ventas->where('id_resolucion', $request->get('id_resolucion'));
        }

        if ($request->get('id_bodega')) {
            $ventas->where('id_bodega', $request->get('id_bodega'));
        }

        if ($request->get('id_producto')) {
            $ventas->whereHas('detalles', function ($query) use($request) {
                $query->where('id_producto', '=', $request->get('id_producto'));
            });
        }

        if ($request->get('id_forma_pago')) {
            $ventas->whereHas('pagos', function ($query) use($request) {
                $query->where('id_forma_pago', '=', $request->get('id_forma_pago'));
            });
        }

        if ($request->get('id_usuario')) {
            $ventas->where('created_by', $request->get('id_usuario'));
        }

        $dataVentas = $ventas->get();
        $totalDataVenta = $this->queryTotalesVentaCosto($request)->select(
            DB::raw("SUM(FVD.cantidad) AS total_productos_cantidad"),
            DB::raw("SUM(FP.precio_inicial * FVD.cantidad) AS total_costo"),
            DB::raw("SUM(FVD.total) AS total_venta")
        )->get();
        $totalDataNotas = $this->queryTotalesVentaCosto($request, true)->select(
            DB::raw("SUM(FVD.cantidad) AS total_productos_cantidad"),
            DB::raw("SUM(FP.precio_inicial * FVD.cantidad) AS total_costo"),
            DB::raw("SUM(FVD.total) AS total_venta")
        )->get();

        if ($request->get('detallar_venta') == 'si') {
            $this->generarVentaDetalles($dataVentas);
        } else {
            $this->generarVentaDetalles($dataVentas, false);
        }
        
        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'totalesVenta' => $totalDataVenta,
            'totalesNotas' => $totalDataNotas,
            'iTotalRecords' => $ventas->count(),
            'iTotalDisplayRecords' => $ventas->count(),
            'data' => $this->ventaData,
            'perPage' => $rowperpage,
            'message'=> 'Ventas cargados con exito!'
        ]);
    }

    private function generarVentaDetalles($dataVentas, $detallar = true)
    {
        foreach ($dataVentas as $value) {
            $resolucion = $value->resolucion && $value->resolucion->tipo_resolucion == FacResoluciones::TIPO_FACTURA_ELECTRONICA ? $value->resolucion : null;
            $this->ventaData[] = [
                "id" => $value->id,
                "descripcion" => "",
                "cantidad" => $value->detalles()->sum('cantidad'),
                "costo" => "",
                "nombre_bodega" => $value->id_bodega ? $value->bodega->codigo.' - '.$value->bodega->nombre : "",
                "nombre_completo" => $value->id_cliente ? $value->cliente->nombre_completo : "",
                "documento_referencia" => $value->documento_referencia,
                "fecha_manual" => $value->fecha_manual,
                "subtotal" => $value->subtotal,
                "iva_porcentaje" => "",
                "nombre_vendedor" => $value->id_vendedor ? $value->vendedor->nit->nombre_completo : "",
                "total_iva" => $value->total_iva,
                "descuento_porcentaje" => "",
                "total_descuento" => $value->total_descuento,
                "rete_fuente_porcentaje" => "",
                "total_rete_fuente" => $value->total_rete_fuente,
                "total_factura" => $value->total_factura,
                "anulado" => $value->anulado,
                "fecha_creacion" => $value->fecha_creacion,
                "fecha_edicion" => $value->fecha_edicion,
                "created_by" => $value->created_by,
                "updated_by" => $value->updated_by,
                "detalle" => $detallar ? false : true,
                "resolucion" => $resolucion,
                'fe_codigo_identificador' => $value->fe_codigo_identificador
            ];
            if ($detallar) {
                foreach ($value->detalles as $ventaDetalle) {
                    $this->ventaData[] = [
                        "id" => "",
                        "descripcion" => $ventaDetalle->descripcion,
                        "cantidad" => $ventaDetalle->cantidad,
                        "costo" => $ventaDetalle->costo,
                        "subtotal" => $ventaDetalle->subtotal,
                        "nombre_bodega" => "",
                        "nombre_completo" => "",
                        "documento_referencia" => "",
                        "fecha_manual" => "",
                        "iva_porcentaje" => $ventaDetalle->iva_porcentaje,
                        "nombre_vendedor" => "",
                        "total_iva" => $ventaDetalle->iva_valor,
                        "descuento_porcentaje" => $ventaDetalle->descuento_porcentaje,
                        "total_descuento" => $ventaDetalle->descuento_valor,
                        "rete_fuente_porcentaje" => "",
                        "total_rete_fuente" => "",
                        "total_factura" => $ventaDetalle->total,
                        "anulado" => "",
                        "fecha_creacion" => "",
                        "fecha_edicion" => "",
                        "created_by" => "",
                        "updated_by" => "",
                        "detalle" => true,
                        "resolucion" => null,
                        'fe_codigo_identificador' => null
                    ];
                }
            }
        }
    }

    private function addFormaPago($documentoReferencia, $formaPago, $nit, $pagoItem, $venta, $valor, $saldo)
    {
        FacVentaPagos::create([
            'id_venta' => $venta->id,
            'id_forma_pago' => $pagoItem->id,
            'valor' => $valor,
            'saldo' => $saldo,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        $doc = new DocumentosGeneral([
            'id_cuenta' => $formaPago->cuenta->id,
            'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
            'id_centro_costos' => $formaPago->cuenta->exige_centro_costos ? $venta->id_centro_costos : null,
            'concepto' => 'TOTAL: '.$formaPago->cuenta->exige_concepto ? $nit->nombre_nit.' - '.$venta->documento_referencia : null,
            'documento_referencia' => $documentoReferencia,
            'debito' => $valor,
            'credito' => $valor,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        return $doc;
    }

    public function read(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        $facturas = FacVentas::orderBy('id', 'DESC')
            ->with(
                'bodega',
                'cliente',
                'comprobante',
                'centro_costo',
            )
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        if ($request->get('consecutivo')) {
            $facturas->where('consecutivo', $request->get('consecutivo'));
        }

        if ($request->get('id_cliente')) {
            $facturas->where('id_cliente', $request->get('id_cliente'));
        }

        if ($request->get('id_bodega')) {
            $facturas->where('id_bodega', $request->get('id_bodega'));
        }

        if ($request->get('id_resolucion')) {
            $facturas->where('id_resolucion', $request->get('id_resolucion'));
        }

        $facturas->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::VENTA_NACIONAL);

        $facturasTotals = $facturas->get();

        $facturasPaginate = $facturas->skip($start)
            ->take(10);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $facturasTotals->count(),
            'iTotalDisplayRecords' => $facturasTotals->count(),
            'data' => $facturasPaginate->get(),
            'perPage' => 10,
            'message'=> 'Facturas generados con exito!'
        ]);
    }

    private function createFacturaVenta ($request)
    {
        $this->calcularTotales($request->get('productos'));
        $this->calcularFormasPago($request->get('pagos'));
        $this->bodega = FacBodegas::whereId($request->get('id_bodega'))->first();
        
        $venta = FacVentas::create([
            'id_cliente' => $request->get('id_cliente'),
            'id_resolucion' => $request->get('id_resolucion'),
            'id_comprobante' => $this->resolucion->comprobante->id,
            'id_bodega' => $request->get('id_bodega'),
            'id_centro_costos' => $this->bodega->id_centro_costos,
            'id_vendedor' => $request->get('id_vendedor'),
            'fecha_manual' => $request->get('fecha_manual'),
            'consecutivo' => $request->get('consecutivo'),
            'documento_referencia' => $request->get('documento_referencia'),
            'subtotal' => $this->totalesFactura['subtotal'],
            'total_iva' => $this->totalesFactura['total_iva'],
            'total_descuento' => $this->totalesFactura['total_descuento'],
            'total_rete_fuente' => $this->totalesFactura['total_rete_fuente'],
            'total_cambio' => $this->totalesPagos['total_cambio'],
            'porcentaje_rete_fuente' => $this->totalesFactura['porcentaje_rete_fuente'],
            'codigo_tipo_documento_dian' => CodigoDocumentoDianTypes::VENTA_NACIONAL,
            'total_factura' => $this->totalesFactura['total_factura'],
            'observacion' => $request->get('observacion'),
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);

        return $venta;
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

    public function showPdf(Request $request, $id)
    {
        $factura = FacVentas::whereId($id)
            ->with('resolucion')
            ->first();

        if(!$factura) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'La factura no existe'
            ], 422);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
        if ($factura->resolucion->tipo_impresion == 0) {
            $data = (new VentasPdf($empresa, $factura))->buildPdf()->getData();
            return view('pdf.facturacion.ventas-pos', $data);
        }
 
        return (new VentasPdf($empresa, $factura))
            ->buildPdf()
            ->showPdf();
    }

    public function showPdfZ(Request $request)
    {
        // $factura = FacVentas::whereId($id)
        //     ->with('resolucion')
        //     ->first();

        // if(!$factura) {
        //     return response()->json([
        //         'success'=>	false,
        //         'data' => [],
        //         'message'=> 'La factura no existe'
        //     ], 422);
        // }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        $data = (new VentasInformeZ($empresa, $request->all()))->buildPdf()->getData();

        return view('pdf.facturacion.ventas-informez-pos', $data);
    }

    public function facturacionElectronica(Request $request)
    {
        $rules = [
			'id_venta' => "required|exists:sam.fac_ventas,id",
		];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        $venta = FacVentas::where('id', $request->id_venta)->first();
        $empresa = Empresa::where('id', $request->user()->id_empresa)->first();

        if ($venta->fe_codigo_identificador) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['factura_electronica' => ['mensaje' => "La factura $venta->consecutivo ya fue emitida."]]
            ], 422);
		}

        try {
            DB::connection('sam')->beginTransaction();
            
            if ($venta->fe_zip_key) {

                $url = "http://localhost:6666/api/ubl2.1/status/zip/{$venta->fe_zip_key}";

                $bearerToken = VariablesEntorno::where('nombre', 'token_key_fe')->first();
			    $bearerToken = $bearerToken ? $bearerToken->valor	: '';

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Authorization' => 'Bearer ' . $bearerToken
                ])->post($url);

                $data = (object) $response->json();

                info(json_encode($data));

                $dianResponse = $data->ResponseDian['Envelope']['Body']['GetStatusZipResponse']['GetStatusZipResult']['DianResponse'];
                $isValid = $dianResponse['IsValid'];

                if ($isValid == 'true') {
                    $venta = $this->SetFeFields($venta, $dianResponse['XmlDocumentKey'], $empresa->nit);
		            $venta->save();

                    DB::connection('sam')->commit();

                    return response()->json([
                        'success'=>	true,
                        'data' => [],
                        'message'=> 'Factura electrónica enviada!'
                    ], 200);
                }
            }

            $ventaElectronica = (new VentaElectronicaSender($venta))->send();
            
            if ($ventaElectronica["status"] >= 400) {
                if ($ventaElectronica["zip_key"]) {
                    $venta->fe_zip_key = $ventaElectronica["zip_key"];
                    $venta->save();

                    if ($ventaElectronica["message_object"] == 'Batch en proceso de validación.') {
                        //JOB CONSULTAR FACTURA EN 1MN
                        ProcessConsultarFE::dispatch($venta->id, $ventaElectronica["zip_key"], $request->user()->id, $empresa->id);

                        DB::connection('sam')->commit();

                        return response()->json([
                            "success" => false,
                            'data' => [],
                            "message" => 'Batch en proceso de validación, el sistema le notificará una vez haya consultado la información'
                        ], 300);
                    }
                }

                DB::connection('sam')->commit();
                
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $ventaElectronica['message_object']
                ], 422);
            }

            if ($ventaElectronica["status"] == 200) {
                $feSended = $ventaElectronica['status'] == 200;
                $hasCufe = (isset($ventaElectronica['cufe']) && $ventaElectronica['cufe']);

                if($feSended || $hasCufe){
                    $ventaElectronica['status'] = 200;
                    $venta = $this->SetFeFields($venta, $ventaElectronica['cufe'], $empresa->nit);
                    $venta->fe_zip_key = $ventaElectronica['zip_key'];
                    $venta->save();
                }
            }

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => [],
				'message'=> 'Factura electrónica enviada!'
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

    public function sendNotification(Request $request)
	{

        $rules = [
			'id_venta' => "required|exists:sam.fac_ventas,id",
		];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }
        
		try {
            
            $venta = FacVentas::with('cliente')
                ->where('id', $request->id_venta)
                ->first();

            if ($this->isFe($venta) && !$venta->cufe) {
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => "La factura electrónica $venta->documento_referencia_fe no tiene cufe generado.",
                ], 422);
            }

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            $pdf = (new VentasPdf($empresa, $venta))
				->buildPdf()
				->getPdf();

            $email = $request->get('email') ?: $venta->cliente->email;

            $this->sendEmailFactura(
                $request->user()['has_empresa'],
                $email,
                $venta,
                $pdf
            );
            
            return response()->json([
				'success'=>	true,
				'data' => [],
				'message'=> 'Factura enviada con exito!'
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

    private function isAnticiposDocumentoRefe($formaPago, $idNit)
    {
        $tiposCuenta = $formaPago->cuenta->tipos_cuenta;
        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == 8) {
                $anticipoCuenta = (new Extracto(
                    $idNit,
                    null,
                    null,
                    Carbon::now()->format('Y-m-d H:i:s'),
                    $formaPago->cuenta->id
                ))->anticiposDiscriminados()->get();

                return $anticipoCuenta;
            }
        }

        return [];
    }

    private function calcularTotales ($productos)
    {
        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();
        $ivaIncluido = $ivaIncluido ? $ivaIncluido->valor : false;
        
        foreach ($productos as $producto) {
            $producto = (object)$producto;

            $productoDb = FacProductos::where('id', $producto->id_producto)
                ->with(
                    'familia.cuenta_venta',
                    'familia.cuenta_venta_retencion.impuesto',
                    'familia.cuenta_venta_iva.impuesto',
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

            $iva = 0;
            $costo = $producto->costo;
            $totalPorCantidad = $producto->cantidad * $costo;
            $cuentaIva = $productoDb->familia->cuenta_venta_iva;

            if ($cuentaIva && $cuentaIva->impuesto) {
                $impuesto = $cuentaIva->impuesto;
                
                if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente']) {
                    $this->totalesFactura['porcentaje_iva'] = floatval($impuesto->porcentaje);
                    $this->totalesFactura['id_cuenta_iva'] = $cuentaIva->id;
                }

                $iva = (($totalPorCantidad - $producto->descuento_valor) * ($this->totalesFactura['porcentaje_iva'] / 100));
                if ($ivaIncluido) {
                    $iva = round(($totalPorCantidad - $producto->descuento_valor) - (($totalPorCantidad - $producto->descuento_valor) / (1 + ($this->totalesFactura['porcentaje_iva'] / 100))), 2);
                }
            }

            if ($ivaIncluido && array_key_exists('porcentaje_iva', $this->totalesFactura)) {
                $costo = round((float)$producto->costo / (1 + ($this->totalesFactura['porcentaje_iva'] / 100)), 2);
            }

            $subtotal = ($producto->cantidad * $costo) - $producto->descuento_valor;
            $this->totalesFactura['subtotal']+= $subtotal;
            $this->totalesFactura['total_iva']+= $iva;
            $this->totalesFactura['total_descuento']+= $producto->descuento_valor;
            $this->totalesFactura['total_factura']+= $subtotal + $iva;
        }

        if ($this->totalesFactura['total_factura'] >= $this->totalesFactura['tope_retencion'] && $this->totalesFactura['porcentaje_rete_fuente'] > 0) {
            $total_rete_fuente = $ivaIncluido ? $this->totalesFactura['total_factura'] * ($this->totalesFactura['porcentaje_rete_fuente'] / 100) : $this->totalesFactura['subtotal'] * ($this->totalesFactura['porcentaje_rete_fuente'] / 100);
            $this->totalesFactura['total_rete_fuente'] = round($total_rete_fuente);
            $this->totalesFactura['total_factura'] = round($this->totalesFactura['total_factura'] - $total_rete_fuente, 1);
        } else {
            $this->totalesFactura['id_cuenta_rete_fuente'] = null;
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

    private function queryTotalesVentaCosto($request, $notas = false)
    {
        return DB::connection('sam')->table('fac_ventas AS FV')
            ->leftJoin('fac_venta_detalles AS FVD', 'FV.id', 'FVD.id_venta')
            ->leftJoin('fac_productos AS FP', 'FVD.id_producto', 'FP.id')
            ->leftJoin('fac_venta_pagos AS FVP', 'FV.id', 'FVP.id_venta')
            ->when(true, function ($query) use ($notas) {
                if ($notas) {
                    $query->whereNotNull('id_factura');
                } else {
                    $query->whereNull('id_factura');
                }
            })
            ->when($request->get('id_cliente') ? true : false, function ($query) use ($request) {
                $query->where('FV.id_cliente', $request->get('id_cliente'));
            })
            ->when($request->get('fecha_desde') ? true : false, function ($query) use ($request) {
                $query->whereDate('FV.fecha_manual', '>=', $request->get('fecha_desde'));
            })
            ->when($request->get('fecha_hasta') ? true : false, function ($query) use ($request) {
                $query->whereDate('FV.fecha_manual', '<=', $request->get('fecha_hasta'));
            })
            ->when($request->get('factura') ? true : false, function ($query) use ($request) {
                $query->where('FV.documento_referencia', 'LIKE', '%'.$request->get('factura').'%');
            })
            ->when($request->get('id_resolucion') ? true : false, function ($query) use ($request) {
                $query->where('FV.id_resolucion', $request->get('id_resolucion'));
            })
            ->when($request->get('id_bodega') ? true : false, function ($query) use ($request) {
                $query->where('FV.id_bodega', $request->get('id_bodega'));
            })
            ->when($request->get('id_forma_pago') ? true : false, function ($query) use ($request) {
                $query->where('FVP.id_forma_pago', $request->get('id_forma_pago'));
            })
            ->when($request->get('id_usuario') ? true : false, function ($query) use ($request) {
                $query->where('FV.created_by', $request->get('id_usuario'));
            });
    }

}