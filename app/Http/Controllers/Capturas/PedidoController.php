<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Jobs\ProcessConsultarFE;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//HELPERS
use App\Helpers\Documento;
use App\Helpers\Printers\PedidosPdf;
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
use App\Models\Sistema\Ubicacion;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\FacPedidos;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacFamilias;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacVentaPagos;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Empresas\UsuarioPermisos;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\FacPedidoDetalles;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class PedidoController extends Controller
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
            'familias' => FacFamilias::get(),
            'cliente' => Nits::with('vendedor.nit')->where('numero_documento', 'LIKE', '22222222%')->first(),
            'bodegas' => FacBodegas::whereIn('id', $bodegas)->get(),
            'ubicaciones' => Ubicacion::with('pedido')->get(),
            'resolucion' => FacResoluciones::whereIn('id', $resoluciones)->get(),
            'iva_incluido' => $ivaIncluido ? $ivaIncluido->valor : '',
            'vendedores_pedidos' => $vendedorVentas ? $vendedorVentas->valor : ''
        ];

        return view('pages.capturas.pedido.pedido-view', $data);
    }
    
    public function create (Request $request)
    {
        $rules = [
            'id_cliente' => 'required|exists:sam.nits,id',
            'id_bodega' => 'required|exists:sam.fac_bodegas,id',
            'consecutivo' => 'required|string',
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
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pedidoEditado = null;

        if (!$request->get('id_pedido')) {
            $consecutivo = $this->getNextConsecutiveBodega($request->get('id_bodega'));
    
            $request->request->add([
                'consecutivo' => $consecutivo
            ]);
        } else {
            $pedidoEditado =  FacPedidos::where('id', $request->get('id_pedido'))->first();
        }

        if ($pedidoEditado && $pedidoEditado->id_venta) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>["Pedido" => ["El pedido ya ha sido facturado"]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $request->all();
        $empresa = Empresa::where('id', $request->user()->id_empresa)->first();

        try {
            DB::connection('sam')->beginTransaction();

            if ($request->get('id_pedido')) {
                $pedidoEditado->detalles()?->delete();
                //ACTUALIZAR PEDIDO
                $pedido = $this->actualizarPedidoVenta($pedidoEditado, $request);
            } else {
                //CREAR FACTURA PEDIDO
                $pedido = $this->createPedidoVenta($request);
            }
            
            //AGREGAR DETALLE DE PRODUCTOS
            foreach ($request->get('productos') as $producto) {
                $producto = (object)$producto;
                $nit = $this->findCliente($pedido->id_cliente);
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
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                //CREAR PEDIDO DETALLE
                FacPedidoDetalles::create([
                    'id_pedido' => $pedido->id,
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
            }

            if (!$request->get('id_pedido')) {
                $this->updateConsecutivoBodega($request->get('id_bodega'), $request->get('consecutivo'));
            }

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => $pedido,
				'message'=> 'Pedido guardado con exito!'
			], Response::HTTP_OK);

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function venta (Request $request)
    {
        $rules = [
            'id_cliente' => 'required|exists:sam.nits,id',
            'id_bodega' => 'required|exists:sam.fac_bodegas,id',
            'fecha_manual' => 'required|date',
            'consecutivo' => 'required|string',
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
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $pedido = null;
        $nuevoPedido = false;

        if ($request->get('id_pedido')) {
            $pedido = FacPedidos::where('id', $request->get('id_pedido'))->first();
        }
        
        if ($pedido && $pedido->id_venta) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>["Pedido" => ["El pedido ya ha sido facturado"]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->resolucion = FacResoluciones::whereId($request->get('id_resolucion'))
            ->with('comprobante')
            ->first();

        if (!$this->resolucion->isValid) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>["Resolución" => ["La resolución {$this->resolucion->nombre_completo} está agotada"]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
            
            //CREAR FACTURA PEDIDO
            if (!$pedido) {
                $nuevoPedido = true;
                $pedido = FacPedidos::create([
                    'id_cliente' => $request->get('id_cliente'),
                    'id_bodega' => $request->get('id_bodega'),
                    'id_centro_costos' => $this->bodega->id_centro_costos,
                    'id_ubicacion' => $request->get('id_ubicacion'),
                    'id_venta' => $venta->id,
                    'id_vendedor' => $request->get('id_vendedor'),
                    'consecutivo' => $request->get('consecutivo_bodegas'),
                    'subtotal' => $this->totalesFactura['subtotal'],
                    'total_iva' => $this->totalesFactura['total_iva'],
                    'total_descuento' => $this->totalesFactura['total_descuento'],
                    'total_rete_fuente' => $this->totalesFactura['total_rete_fuente'],
                    'porcentaje_rete_fuente' => $this->totalesFactura['porcentaje_rete_fuente'],
                    'total_factura' => $this->totalesFactura['total_factura'],
                    'observacion' => $request->get('observacion'),
                    'estado' => 2,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id,
                ]);
            }

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
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
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

                //CREAR PEDIDO DETALLE
                if ($nuevoPedido) {
                    FacPedidoDetalles::create([
                        'id_pedido' => $pedido->id,
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
                }

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
                            ], Response::HTTP_UNPROCESSABLE_ENTITY);
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
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            $totalProductos = $this->totalesFactura['total_factura'];

            //AGREGAR FORMAS DE PAGO
            foreach ($request->get('pagos') as $pago) {
                $pago = (object)$pago;
                $pagoValor = $pago->id == 1 ? $pago->valor - $this->totalesPagos['total_cambio'] : $pago->valor;

                $formaPago = $this->findFormaPago($pago->id);
                $totalProductos-= $pagoValor;

                FacVentaPagos::create([
                    'id_venta' => $venta->id,
                    'id_forma_pago' => $formaPago->id,
                    'valor' => $pagoValor,
                    'saldo' => $totalProductos,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                $doc = new DocumentosGeneral([
                    'id_cuenta' => $formaPago->cuenta->id,
                    'id_nit' => $formaPago->cuenta->exige_nit ? $venta->id_cliente : null,
                    'id_centro_costos' => $formaPago->cuenta->exige_centro_costos ? $venta->id_centro_costos : null,
                    'concepto' => 'TOTAL: '.$formaPago->cuenta->exige_concepto ? $nit->nombre_nit.' - '.$venta->documento_referencia : null,
                    'documento_referencia' => $formaPago->cuenta->exige_documento_referencia ? $venta->documento_referencia : null,
                    'debito' => $formaPago->cuenta->naturaleza_ventas == PlanCuentas::DEBITO ? $pagoValor : 0,
                    'credito' => $formaPago->cuenta->naturaleza_ventas == PlanCuentas::CREDITO ? $pagoValor : 0,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
            }

            $this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));
            if ($nuevoPedido) $this->updateConsecutivoBodega($request->get('id_bodega'), $request->get('consecutivo_bodegas'));
            
            if (!$documentoGeneral->save()) {

				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], Response::HTTP_UNPROCESSABLE_ENTITY);
			}

            if ($request->get('id_pedido')) {
                FacPedidos::where('id', $request->get('id_pedido'))
                    ->update([
                        'id_venta' => $venta->id,
                        'estado' => 2
                    ]);
            }

            //FACTURAR ELECTRONICAMENTE
            if ($this->resolucion->tipo_resolucion == FacResoluciones::TIPO_FACTURA_ELECTRONICA) {
                $ventaElectronica = (new VentaElectronicaSender($venta))->send();

                if ($ventaElectronica["status"] >= 400) {
                    if ($ventaElectronica["zip_key"]) {
                        $venta->fe_zip_key = $ventaElectronica["zip_key"];
                        $venta->save();
    
                        if ($ventaElectronica["message_object"] == 'Batch en proceso de validación.') {
                            //JOB CONSULTAR FACTURA EN 1MN
                            info('Batch en proceso de validación.');
                            ProcessConsultarFE::dispatch($venta->id, $ventaElectronica["zip_key"], $request->user()->id, $empresa->id)->delay(now()->addSeconds(10));
                        }
                    }
                }

                if ($ventaElectronica['status'] == 200) {
                    $feSended = $ventaElectronica['status'] == 200;
                    $hasCufe = (isset($ventaElectronica['cufe']) && $ventaElectronica['cufe']);
    
                    if($feSended || $hasCufe){
                        $ventaElectronica['status'] = 200;
                        $venta = $this->SetFeFields($venta, $ventaElectronica['cufe'], $empresa->nit);
                        $venta->fe_zip_key = $ventaElectronica['zip_key'];
                        $venta->save();
                    }
                }
            }

            $feSended = false;
            $hasCufe = false;

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => $documentoGeneral->getRows(),
				'impresion' => $this->resolucion->comprobante->imprimir_en_capturas ? $venta->id : '',
				'message'=> 'Venta creada con exito!'
			], Response::HTTP_OK);

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function find (Request $request)
    {
        try {
            $pedido = FacPedidos::with(
                'cliente',
                'venta',
                'bodega',
                'detalles.cuenta_retencion.impuesto',
                'detalles.producto'
            );

            if ($request->get('id_ubicacion')) {
                $pedido->where('id_ubicacion', $request->get('id_ubicacion'))
                    ->where('estado', 1);
            } else {
                $pedido->where('id_bodega', $request->get('id_bodega'))
                    ->where('consecutivo', $request->get('consecutivo'));
            }

            return response()->json([
                "success"=>false,
                'data' => $pedido->first(),
                "message"=> 'Información cargada con exito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => $pedido,
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function delete (Request $request)
    {
        $rules = [
            'id' => 'required|exists:sam.fac_pedidos,id',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::connection('sam')->beginTransaction();

            FacPedidos::where('id', $request->get('id'))->delete();
            FacPedidoDetalles::where('id_pedido', $request->get('id'))->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Pedido eliminado con exito!'
            ]);

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function showPdf(Request $request, $id)
    {
        $pedido = FacPedidos::whereId($id)->first();

        if(!$pedido) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'El pedido no existe'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        $data = (new PedidosPdf($empresa, $pedido))->buildPdf()->getData();
        return view('pdf.facturacion.pedidos-pos', $data);
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

    private function createPedidoVenta ($request)
    {
        $this->calcularTotales($request->get('productos'));

        $this->bodega = FacBodegas::whereId($request->get('id_bodega'))->first();
        
        $pedido = FacPedidos::create([
            'id_cliente' => $request->get('id_cliente'),
            'id_bodega' => $request->get('id_bodega'),
            'id_centro_costos' => $this->bodega->id_centro_costos,
            'id_ubicacion' => $request->get('id_ubicacion'),
            'id_vendedor' => $request->get('id_vendedor'),
            'consecutivo' => $request->get('consecutivo'),
            'subtotal' => $this->totalesFactura['subtotal'],
            'total_iva' => $this->totalesFactura['total_iva'],
            'total_descuento' => $this->totalesFactura['total_descuento'],
            'total_rete_fuente' => $this->totalesFactura['total_rete_fuente'],
            'porcentaje_rete_fuente' => $this->totalesFactura['porcentaje_rete_fuente'],
            'total_factura' => $this->totalesFactura['total_factura'],
            'estado' => 1,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);

        return $pedido;
    }

    private function actualizarPedidoVenta ($pedido, $request)
    {
        $this->calcularTotales($request->get('productos'));

        $this->bodega = FacBodegas::whereId($request->get('id_bodega'))->first();

        $pedido->id_cliente = $request->get('id_cliente');
        $pedido->id_bodega = $request->get('id_bodega');
        $pedido->id_centro_costos = $this->bodega->id_centro_costos;
        $pedido->id_ubicacion = $request->get('id_ubicacion');
        $pedido->id_vendedor = $request->get('id_vendedor');
        $pedido->consecutivo = $request->get('consecutivo');
        $pedido->subtotal = $this->totalesFactura['subtotal'];
        $pedido->total_iva = $this->totalesFactura['total_iva'];
        $pedido->total_descuento = $this->totalesFactura['total_descuento'];
        $pedido->total_rete_fuente = $this->totalesFactura['total_rete_fuente'];
        $pedido->porcentaje_rete_fuente = $this->totalesFactura['porcentaje_rete_fuente'];
        $pedido->total_factura = $this->totalesFactura['total_factura'];
        $pedido->estado = 1;
        $pedido->created_by = request()->user()->id;
        $pedido->save();

        return $pedido;
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
                $costo = (float)$producto->costo / (1 + ($this->totalesFactura['porcentaje_iva'] / 100));
            }

            $subtotal = ($producto->cantidad * $costo) - $producto->descuento_valor;
            // dd($producto->cantidad, $costo);
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

        $this->totalesFactura['subtotal'] = round($this->totalesFactura['subtotal'], 2);
        $this->totalesFactura['total_factura'] = round($this->totalesFactura['total_factura'], 2);
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