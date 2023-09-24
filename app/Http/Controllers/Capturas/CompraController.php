<?php

namespace App\Http\Controllers\Capturas;

use DB;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Printers\ComprasPdf;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\FacCompras;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacCompraDetalles;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class CompraController extends Controller
{
    use BegConsecutiveTrait;

    protected $bodega = null;
	protected $messages = null;
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
        "cuenta_compra" => ["valor" => "subtotal"],
		"cuenta_compra_descuento" => ["valor" => "descuento_valor"],
        "cuenta_compra_iva" => ["valor" => "iva_valor"],
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

    public function index ()
    {
        $data = [
            'bodegas' => FacBodegas::first()
        ];
        return view('pages.capturas.compra.compra-view', $data);
    }

    public function indexInforme ()
    {
        $data = [
        ];
        return view('pages.contabilidad.compras.compras-view', $data);
    }

    public function create (Request $request)
    {
        $idCuentaAnticipos = VariablesEntorno::where('nombre', 'id_cuenta_anticipos')->first();

        if(!$idCuentaAnticipos || !$idCuentaAnticipos->valor) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>'La Cuenta anticipos en compras no se encuentra configurada!'
            ], 422);
        } else {
            $request->request->add(['id_cuenta_anticipos' => $idCuentaAnticipos->valor]);
        }

        $comprobanteCompras = VariablesEntorno::where('nombre', 'id_comprobante_compra')->first();

        if(!$comprobanteCompras || !$comprobanteCompras->valor) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>'El Comprobante de compras no se encuentra configurado!'
            ], 422);
        } else {
            $consecutivo = $this->getNextConsecutive($comprobanteCompras->id, $request->get('fecha_manual'));
            $request->request->add([
                'id_comprobante' => $comprobanteCompras->valor,
                'consecutivo' => $consecutivo
            ]);
        }

        $rules = [
            'id_proveedor' => 'required|exists:sam.nits,id',
            'id_bodega' => 'required|exists:sam.fac_bodegas,id',
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'id_cuenta_anticipos' => 'required|exists:sam.plan_cuentas,id',
            'fecha_manual' => 'required|date',
            'documento_referencia' => 'required |string',
            'productos' => 'array|required',
            'productos.*.id_producto' => [
                'required',
                'exists:sam.fac_productos,id',
                function ($attribute, $value, $fail) {
					$producto = FacProductos::whereId($value)
                        ->with('familia')
                        ->first();

                    if (!$producto->familia->id_cuenta_compra) {
                        $fail("La familia (".$producto->familia->codigo." - ".$producto->familia->nombre.") no tiene cuenta compra configurada");
                    }
				}
            ],
            'productos.*.cantidad' => 'required|min:1',
            'productos.*.costo' => 'required|min:0',
            'productos.*.descuento_porcentaje' => 'required|min:0|max:99',
            'productos.*.descuento_valor' => 'required|min:0',
            'productos.*.iva_porcentaje' => 'required|min:0|max:99',
            'productos.*.iva_valor' => 'required|min:0',
            'productos.*.total' => 'required|min:0',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->messages()
            ], 422);
        }

        try {

            DB::connection('sam')->beginTransaction();
            //CREAR FACTURA COMPRAR
            $compra = $this->createFacturaCompra($request);
            //CREAR MOVIMIENTO BODEGAS
            foreach ($request->get('productos') as $producto) {
                $producto = (object)$producto;
                
            }
            //GUARDAR DETALLE & MOVIMIENTO CONTABLE COMPRAS
            $documentoGeneral = new Documento(
                $request->get('id_comprobante'),
                $compra,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            foreach ($request->get('productos') as $producto) {
                $producto = (object)$producto;
    
                $nit = $this->findProveedor($compra->id_proveedor);
                $productoDb = $this->findProducto($producto->id_producto);
                //CREAR COMPRA DETALLE
                FacCompraDetalles::create([
                    'id_compra' => $compra->id,
                    'id_cuenta_compra' => $productoDb->familia->id_cuenta_compra,
                    'id_cuenta_compra_retencion' => $productoDb->familia->id_cuenta_compra_retencion,
                    'id_cuenta_compra_iva' => $productoDb->familia->id_cuenta_compra_iva,
                    'id_cuenta_compra_descuento' => $productoDb->familia->id_cuenta_compra_descuento,
                    'descripcion' => $productoDb->codigo.' - '.$productoDb->nombre,
                    'cantidad' => $producto->cantidad,
                    'costo' => $producto->costo,
                    'subtotal' => $producto->costo * $producto->cantidad,
                    'descuento_porcentaje' => $producto->descuento_porcentaje,
                    'descuento_valor' => $producto->descuento_valor,
                    'iva_porcentaje' => $producto->iva_porcentaje,
                    'iva_valor' => $producto->iva_valor,
                    'total' => $producto->total,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
                //AGREGAR MOVIMIENTO CONTABLE
                foreach ($this->cuentasContables as $cuentaKey => $cuenta) {
                    $cuentaRecord = $productoDb->familia->{$cuentaKey};
    
                    $keyTotalItem = $cuenta["valor"];
    
                    $doc = new DocumentosGeneral([
                        "id_cuenta" => $cuentaRecord->id,
                        "id_nit" => $cuentaRecord->exige_nit ? $compra->id_proveedor : null,
                        "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $compra->id_centro_costos : null,
                        "concepto" => 'COMPRA: '.$cuentaRecord->exige_concepto ? $nit->nombre_nit.' - '.$compra->documento_referencia : null,
                        "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $compra->documento_referencia : null,
                        "debito" => $cuentaRecord->naturaleza_cuenta == PlanCuentas::DEBITO ? $producto->{$keyTotalItem} : 0,
                        "credito" => $cuentaRecord->naturaleza_cuenta == PlanCuentas::CREDITO ? $producto->{$keyTotalItem} : 0,
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);
                    $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_cuenta);
                }
                //AGREGAR MOVIMIENTO BODEGA
                $bodegaProducto = FacProductosBodegas::where('id_bodega', $this->bodega->id)
                    ->where('id_producto', $producto->id_producto)
                    ->first();
                
                if (!$bodegaProducto) {
                    $bodegaProducto = FacProductosBodegas::create([
                        'id_producto' => $producto->id_producto,
                        'id_bodega' => $compra->id_bodega,
                        'cantidad' => 0,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
                }
    
                $movimiento = new FacProductosBodegasMovimiento([
                    'id_producto' => $producto->id_producto,
                    'id_bodega' => $compra->id_bodega,
                    'cantidad' => $producto->cantidad,
                    'tipo_tranferencia' => 1,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
    
                $movimiento->relation()->associate($compra);
                $compra->bodegas()->save($movimiento);
    
                $bodegaProducto->cantidad+= $producto->cantidad;
                $bodegaProducto->save();
            }
            //AGREGAR RETEFUENTE
            if ($this->totalesFactura['total_rete_fuente']) {
                $cuentaRetencion = PlanCuentas::whereId($this->totalesFactura['id_cuenta_rete_fuente'])->first();

                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRetencion->id,
                    "id_nit" => $cuentaRetencion->exige_nit ? $compra->id_proveedor : null,
                    "id_centro_costos" => $cuentaRetencion->exige_centro_costos ? $compra->id_centro_costos : null,
                    "concepto" => 'TOTAL: '.$cuentaRetencion->exige_concepto ? $nit->nombre_nit.' - '.$compra->documento_referencia : null,
                    "documento_referencia" => $cuentaRetencion->exige_documento_referencia ? $compra->documento_referencia : null,
                    "debito" => $cuentaRetencion->naturaleza_cuenta == PlanCuentas::DEBITO ? $this->totalesFactura['total_rete_fuente'] : 0,
                    "credito" => $cuentaRetencion->naturaleza_cuenta == PlanCuentas::CREDITO ? $this->totalesFactura['total_rete_fuente'] : 0,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $cuentaRetencion->naturaleza_cuenta);
            }
            //AGREGAR COMPRA EN ANTICIPOS CLIENTES
            $cuentaAnticipos = PlanCuentas::whereId($idCuentaAnticipos->valor)->first();
            
            $doc = new DocumentosGeneral([
                "id_cuenta" => $cuentaAnticipos->id,
                "id_nit" => $cuentaAnticipos->exige_nit ? $compra->id_proveedor : null,
                "id_centro_costos" => $cuentaAnticipos->exige_centro_costos ? $compra->id_centro_costos : null,
                "concepto" => 'TOTAL COMPRA: '.$cuentaAnticipos->exige_concepto ? $nit->nombre_nit.' - '.$compra->documento_referencia : null,
                "documento_referencia" => $cuentaAnticipos->exige_documento_referencia ? $compra->documento_referencia : null,
                "debito" => $cuentaAnticipos->naturaleza_compras == PlanCuentas::DEBITO ? $compra->total_factura : 0,
                "credito" => $cuentaAnticipos->naturaleza_compras == PlanCuentas::CREDITO ? $compra->total_factura : 0,
                "created_by" => request()->user()->id,
                "updated_by" => request()->user()->id
            ]);
            $documentoGeneral->addRow($doc, $cuentaAnticipos->naturaleza_compras);

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
				'impresion' => $comprobanteCompras->imprimir_en_capturas ? $compra->id : '',
				'message'=> 'Compra creada con exito!'
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

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

		$compras = FacCompras::skip($start)
            ->with(
                'bodega',
                'proveedor',
                'comprobante'
            )
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            )
            ->take($rowperpage);

        if($columnName){
            $compras->orderBy($columnName,$columnSortOrder);
        }
        
        if ($request->get('consecutivo')) {
            $compras->where('consecutivo', $request->get('consecutivo'));
        }

        if ($request->get('fecha_manual')) {
            $compras->where('fecha_manual', $request->get('fecha_manual'));
        }

        if ($request->get('id_proveedor')) {
            $compras->where('id_proveedor', $request->get('id_proveedor'));
        }

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $compras->count(),
            'iTotalDisplayRecords' => $compras->count(),
            'data' => $compras->get(),
            'perPage' => $rowperpage,
            'message'=> 'Compras cargados con exito!'
        ]);
    }

    public function showPdf(Request $request, $id)
    {
        // return $request->user();

        $factura = FacCompras::whereId($id)->first();

        if(!$factura) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'La factura no existe'
            ]);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        // $data = (new ComprasPdf($empresa, $factura))->buildPdf()->getData();
        // return view('pdf.facturacion.compras', $data);
 
        return (new ComprasPdf($empresa, $factura))
            ->buildPdf()
            ->showPdf();
    }

    private function createFacturaCompra ($request)
    {
        $this->calcularTotales($request->get('productos'));

        $this->bodega = FacBodegas::whereId($request->get('id_bodega'))->first();

        $compra = FacCompras::create([
            'id_proveedor' => $request->get('id_proveedor'),
            'id_comprobante' => $request->get('id_comprobante'),
            'id_bodega' => $request->get('id_bodega'),
            'id_centro_costos' => $this->bodega->id_centro_costos,
            'fecha_manual' => $request->get('fecha_manual'),
            'consecutivo' => $request->get('consecutivo'),
            'documento_referencia' => $request->get('documento_referencia'),
            'subtotal' => $this->totalesFactura['subtotal'],
            'total_iva' => $this->totalesFactura['total_iva'],
            'total_descuento' => $this->totalesFactura['total_descuento'],
            'total_rete_fuente' => $this->totalesFactura['total_rete_fuente'],
            'porcentaje_rete_fuente' => $this->totalesFactura['porcentaje_rete_fuente'],
            'total_factura' => $this->totalesFactura['total_factura'],
            'observacion' => '',
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);

        return $compra;
    }

    private function calcularTotales ($productos)
    {
        foreach ($productos as $producto) {
            $producto = (object)$producto;

            $productoDb = FacProductos::where('id', $producto->id_producto)
                ->with(
                    'familia.cuenta_compra',
                    'familia.cuenta_compra_retencion.impuesto',
                    'familia.cuenta_compra_iva.impuesto',
                    'familia.cuenta_compra_descuento'
                )
                ->first();
            
            $cuentaRetencion = $productoDb->familia->cuenta_compra_retencion;
            if ($cuentaRetencion && $cuentaRetencion->impuesto) {
                $impuesto = $cuentaRetencion->impuesto;
                if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente']) {
                    $this->totalesFactura['porcentaje_rete_fuente'] = floatval($impuesto->porcentaje);
                    $this->totalesFactura['tope_retencion'] = floatval($impuesto->base);
                    $this->totalesFactura['id_cuenta_rete_fuente'] = $cuentaRetencion->id;
                }
            }
            $subtotal = $producto->cantidad * $producto->costo;
            $this->totalesFactura['subtotal']+= $subtotal;
            $this->totalesFactura['total_iva']+= $producto->iva_valor;
            $this->totalesFactura['total_descuento']+= $producto->descuento_valor;
            $this->totalesFactura['total_factura']+= $subtotal - $producto->descuento_valor + $producto->iva_valor;
        }

        if ($this->totalesFactura['total_factura'] > $this->totalesFactura['tope_retencion'] && $this->totalesFactura['porcentaje_rete_fuente'] > 0) {
            $total_rete_fuente = ($this->totalesFactura['subtotal'] * $this->totalesFactura['porcentaje_rete_fuente']) / 100;
            $this->totalesFactura['total_rete_fuente'] = $total_rete_fuente;
            $this->totalesFactura['total_factura']-= $total_rete_fuente;
        } else {
            $this->totalesFactura['id_cuenta_rete_fuente'] = null;
        }
    }

    private function findProveedor ($id_proveedor)
    {
        return Nits::whereId($id_proveedor)
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
        return FacProductos::where('id', $id_producto)
            ->with(
                'familia.cuenta_compra',
                'familia.cuenta_compra_retencion.impuesto',
                'familia.cuenta_compra_iva.impuesto',
                'familia.cuenta_compra_descuento'
            )
            ->first();
    }

}
