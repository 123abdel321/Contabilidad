<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\ProcessConsultarFE;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//HELPERS
use App\Helpers\Documento;
use App\Helpers\Printers\ParqueaderoPdf;
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
use App\Models\Sistema\FacFamilias;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacVentaPagos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacParqueadero;
use App\Models\Sistema\FacResoluciones;
use App\Models\Empresas\UsuarioPermisos;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;

class ParqueaderoController extends Controller
{
    use BegConsecutiveTrait;
    use BegDocumentHelpersTrait;
    use BegFacturacionElectronica;

    protected $bodega = null;
    protected $productoDb = null;
    protected $parqueadero = null;
    protected $resolucion = null;
	protected $messages = null;
    protected $ventaData = [];
    protected $totalesPagos = [
        'total_efectivo' => 0,
        'total_otrospagos' => 0,
    ];
    protected $totalesFactura = [
        'tope_retencion' => 0,
        'total_producto' => 0,
        'porcentaje_rete_fuente' => 0,
        'id_cuenta_rete_fuente' => null,
        'subtotal' => 0,
        'total_iva' => 0,
        'fecha_salida' => 0,
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
            'resolucion' => FacResoluciones::whereIn('id', $resoluciones)->get(),
            'iva_incluido' => $ivaIncluido ? $ivaIncluido->valor : '',
            'vendedores_pedidos' => $vendedorVentas ? $vendedorVentas->valor : ''
        ];

        return view('pages.capturas.parqueadero.parqueadero-view', $data);
    }

    public function generate (Request $request) 
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

        $parqueaderos = FacParqueadero::orderBy('id', 'DESC')
            ->with(
                'venta',
                'bodega',
                'cliente',
                'producto.familia.cuenta_venta',
                'producto.familia.cuenta_venta_retencion.impuesto',
                'producto.familia.cuenta_venta_iva.impuesto',
                'producto.familia.cuenta_venta_descuento',
            )
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        if ($request->get('id_nit')) $parqueaderos->where('id_cliente', $request->get('id_nit'));
        if ($request->get('tipo_vehiculo')) $parqueaderos->where('tipo', $request->get('tipo_vehiculo'));
        if ($request->get('placa')) $parqueaderos->where('placa', $request->get('placa'));

        $parqueaderosTotals = $parqueaderos->count();

        $parqueaderosPaginate = $parqueaderos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $parqueaderosTotals,
            'iTotalDisplayRecords' => $parqueaderosTotals,
            'data' => $parqueaderosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Parqueaderos generadas con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'tipo' => 'required',
            'placa' => 'required|min:3|max:200|string',
            'id_nit' => 'required|exists:sam.nits,id',
            'fecha_inicio' => 'required',
            'id_producto' => 'required|exists:sam.fac_productos,id',
            'id_bodega' => 'required|exists:sam.fac_bodegas,id',
            'consecutivo' => 'required',
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

            DB::connection('sam')->beginTransaction();

            $consecutivo = $this->getNextConsecutiveBodegaParqueadero($request->get('id_bodega'));

            $bodega = FacBodegas::whereId($request->get('id_bodega'))->first();

            $parqueadero = FacParqueadero::create([
                'tipo' => $request->get('tipo'),
                'placa' => $request->get('placa'),
                'id_cliente' => $request->get('id_nit'),
                'id_bodega' => $request->get('id_bodega'),
                'consecutivo' => $request->get('consecutivo'),
                'id_producto' => $request->get('id_producto'),
                'id_centro_costos' => $bodega->id_centro_costos,
                'fecha_inicio' => Carbon::now()->format('Y-m-d H:i'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $this->updateConsecutivoParqueadero($request->get('id_bodega'), $consecutivo);

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => $parqueadero->load('cliente', 'producto', 'venta'),
				'message'=> 'Parqueadero guardado con exito!'
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

    public function update (Request $request)
    {
        $rules = [
            'id' => 'required|exists:sam.fac_parqueaderos,id',
            'tipo' => 'required',
            'placa' => 'required|min:3|max:200|string',
            'id_nit' => 'required|exists:sam.nits,id',
            'fecha_inicio' => 'required',
            'id_producto' => 'required|exists:sam.fac_productos,id',
            'id_bodega' => 'required|exists:sam.fac_bodegas,id',
            'consecutivo' => 'required',
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

            DB::connection('sam')->beginTransaction();

            $consecutivo = $this->getNextConsecutiveBodegaParqueadero($request->get('id_bodega'));

            $bodega = FacBodegas::whereId($request->get('id_bodega'))->first();

            $parqueadero = FacParqueadero::where('id', $request->get('id'))->first();
            $parqueadero->tipo = $request->get('tipo');
            $parqueadero->placa = $request->get('placa');
            $parqueadero->id_cliente = $request->get('id_nit');
            $parqueadero->id_bodega = $request->get('id_bodega');
            $parqueadero->consecutivo = $request->get('consecutivo');
            $parqueadero->id_producto = $request->get('id_producto');
            $parqueadero->id_centro_costos = $bodega->id_centro_costos;
            $parqueadero->fecha_inicio = Carbon::parse($request->get('fecha_inicio'))->format('Y-m-d H:i');
            $parqueadero->updated_by = request()->user()->id;

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'data' => $parqueadero->load('cliente', 'producto', 'venta'),
				'message'=> 'Parqueadero actualizado con exito!'
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

    public function venta (Request $request)
    {
        $rules = [
            'id_cliente' => 'required|exists:sam.nits,id',
            'id_bodega' => 'required|exists:sam.fac_bodegas,id',
            'fecha_manual' => 'required|date',
            'consecutivo' => 'required|string',
            'producto' => [
                'required',
                'exists:sam.fac_productos,id',
                function ($attribute, $value, $fail) {
					$detalle = FacProductos::whereId($value)
                        ->with('familia')
                        ->first();
                    
                    if (!$detalle->id_familia) {
                        $fail("El producto (".$detalle->codigo." - ".$detalle->nombre.") no tiene familia venta configurada");
                    } else if (!$detalle->familia->id_cuenta_venta) {
                        $fail("La familia (".$detalle->familia->codigo." - ".$detalle->familia->nombre.") no tiene cuenta venta configurada");
                    }
				}
            ],
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

        $consecutivo = $this->getNextConsecutive($this->resolucion->comprobante->id, $request->get('fecha_manual'));

        $request->request->add([
            'id_comprobante' => $this->resolucion->comprobante->id,
            'consecutivo' => $consecutivo
        ]);

        $empresa = Empresa::where('id', $request->user()->id_empresa)->first();

        try {

            DB::connection('sam')->beginTransaction();

            //BUSCAR PARQUEADERO
            $this->parqueadero = FacParqueadero::where('id', $request->get('id_parqueadero'))->first();

            //CREAR FACTURA VENTA
            $venta = $this->createFacturaVenta($request);

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE VENTAS
            $documentoGeneral = new Documento(
                $this->resolucion->comprobante->id,
                $venta,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            $nit = $this->findCliente($venta->id_cliente);
            //CREAR VENTA DETALLE
            $detalle = FacVentaDetalles::create([
                'id_venta' => $venta->id,
                'id_producto' => $this->productoDb->id,
                'id_cuenta_venta' => $this->productoDb->familia->id_cuenta_venta,
                'id_cuenta_venta_retencion' => $this->productoDb->familia->id_cuenta_venta_retencion,
                'id_cuenta_venta_iva' => $this->productoDb->familia->id_cuenta_venta_iva,
                'id_cuenta_venta_descuento' => $this->productoDb->familia->id_cuenta_venta_descuento,
                'descripcion' => $this->productoDb->codigo.' - '.$this->productoDb->nombre,
                'cantidad' => $this->totalesFactura['total_producto'],
                'costo' => $this->productoDb->precio,
                'subtotal' => $this->totalesFactura['subtotal'],
                'descuento_porcentaje' => 0,
                'descuento_valor' => 0,
                'iva_porcentaje' => $this->totalesFactura['porcentaje_iva'],
                'iva_valor' => $this->totalesFactura['total_iva'],
                'total' => $this->totalesFactura['total_factura'],
                'observacion' => '',
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            //AGREGAR MOVIMIENTO CONTABLE
            foreach ($this->cuentasContables as $cuentaKey => $cuenta) {
                $cuentaRecord = $this->productoDb->familia->{$cuentaKey};
                $keyTotalItem = $cuenta["valor"];
                // dd($keyTotalItem);
                //VALIDAR PRODUCTO INVENTARIO
                if ($this->productoDb->tipo_producto == 1 && $cuentaKey == 'cuenta_inventario') {
                    continue;
                }

                if ($this->productoDb->tipo_producto == 1 && $cuentaKey == 'cuenta_costos') {
                    continue;
                }

                //VALIDAR COSTO PRODUCTO
                if ($this->productoDb->precio_inicial <= 0 && $cuentaKey == 'cuenta_costos') {
                    continue;
                }

                if ($detalle->{$keyTotalItem} > 0) {
                    
                    if(!$cuentaRecord) {
                        DB::connection('sam')->rollback();
                        return response()->json([
                            "success"=>false,
                            'data' => [],
                            "message"=> [$this->productoDb->codigo.' - '.$this->productoDb->nombre => ['La cuenta '.str_replace('cuenta_venta_', '', $cuentaKey). ' no se encuentra configurada en la familia: '. $this->productoDb->familia->codigo. ' - '. $this->productoDb->familia->nombre]]
                        ], 422);
                    }

                    $concepto = "VENTA PARQUEADERO: {$nit->nombre_nit} - {$nit->documento} - {$venta->documento_referencia}";
    
                    $doc = new DocumentosGeneral([
                        "id_cuenta" => $cuentaRecord->id,
                        "id_nit" => $cuentaRecord->exige_nit ? $venta->id_cliente : null,
                        "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $venta->id_centro_costos : null,
                        "concepto" => $cuentaRecord->exige_concepto ? $concepto : null,
                        "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $venta->documento_referencia : null,
                        "debito" => $cuentaRecord->naturaleza_ventas == PlanCuentas::DEBITO ? $detalle->{$keyTotalItem} : 0,
                        "credito" => $cuentaRecord->naturaleza_ventas == PlanCuentas::CREDITO ? $detalle->{$keyTotalItem} : 0,
                        "created_by" => request()->user()->id,
                        "updated_by" => request()->user()->id
                    ]);

                    $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_ventas);
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

            if (!$documentoGeneral->save()) {

				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], 422);
			}

            $this->parqueadero->id_venta = $venta->id;
            $this->parqueadero->fecha_fin = $this->totalesFactura['fecha_salida'];
            $this->parqueadero->save();

            $feSended = false;
            $hasCufe = false;

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

    public function delete (Request $request)
    {
        try {

            DB::connection('sam')->beginTransaction();

            FacParqueadero::where('id', $request->get('id'))->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Parqueadero eliminada con exito!'
            ]);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function showPdf (Request $request, $id)
    {
        $parqueadero = FacParqueadero::whereId($id)
            ->first();

        if(!$parqueadero) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'La parqueadero no existe'
            ], 422);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
        $data = (new ParqueaderoPdf($empresa, $parqueadero))->buildPdf()->getData();
        return view('pdf.facturacion.parqueadero-pos', $data);
 
        // return (new VentasPdf($empresa, $parqueadero))
        //     ->buildPdf()
        //     ->showPdf();
    }

    private function createFacturaVenta ($request)
    {
        $this->calcularTotales($request->get('producto'));
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

    private function calcularTotales ($detalle)
    {
        $ivaIncluido = VariablesEntorno::where('nombre', 'iva_incluido')->first();
        $ivaIncluido = $ivaIncluido ? $ivaIncluido->valor : false;

        $totalProductos = 0;
        $sumarCuartoHora = false;

        $this->productoDb = FacProductos::where('id', $detalle)
            ->with(
                'familia.cuenta_venta',
                'familia.cuenta_venta_retencion.impuesto',
                'familia.cuenta_venta_iva.impuesto',
                'familia.cuenta_venta_descuento'
            )
            ->first();

        $fechaInicio = Carbon::parse($this->parqueadero->fecha_inicio); // Reemplaza con tu fecha de inicio
        $fechaActual = Carbon::now();
        $this->totalesFactura['fecha_salida'] = $fechaActual->format('Y-m-d H:i');
        
        if ($this->productoDb->tipo_tiempo == 1) {

            $diferenciaHoras = $fechaInicio->diffInHours($fechaActual);
            $diferenciaMinutos = $fechaInicio->diffInMinutes($fechaActual) % 60;
            $excedeCuartoHora = $diferenciaMinutos > 15;

            if ($excedeCuartoHora) $diferenciaHoras+= 1;
            else if (!$excedeCuartoHora && $this->productoDb->fraccion_hora) $sumarCuartoHora = true;
            else if (!$excedeCuartoHora && !$this->productoDb->fraccion_hora) $diferenciaHoras+= 1;

            $totalProductos = $diferenciaHoras;
        }

        if ($this->productoDb->tipo_tiempo == 2) {
            $totalProductos = $fechaInicio->diffInDays($fechaActual);
            if (!$totalProductos) $totalProductos = 1;
        }

        if ($this->productoDb->tipo_tiempo == 3) {
            $totalProductos = $fechaInicio->diffInMonths($fechaActual);
            if (!$totalProductos) $totalProductos = 1;
        }

        $this->totalesFactura['total_producto'] = $totalProductos;

        $cuentaRetencion = $this->productoDb->familia->cuenta_venta_retencion;
        if ($cuentaRetencion && $cuentaRetencion->impuesto) {
            $impuesto = $cuentaRetencion->impuesto;
            if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente']) {
                $this->totalesFactura['porcentaje_rete_fuente'] = floatval($impuesto->porcentaje);
                $this->totalesFactura['tope_retencion'] = floatval($impuesto->base);
                $this->totalesFactura['id_cuenta_rete_fuente'] = $cuentaRetencion->id;
            }
        }

        $iva = 0;
        $costo = $this->productoDb->precio;
        $totalPorCantidad = $totalProductos * $costo;
        $cuentaIva = $this->productoDb->familia->cuenta_venta_iva;
        $descuento = 0;

        if ($cuentaIva && $cuentaIva->impuesto) {
            $impuesto = $cuentaIva->impuesto;
            
            if (floatval($impuesto->porcentaje) > $this->totalesFactura['porcentaje_rete_fuente']) {
                $this->totalesFactura['porcentaje_iva'] = floatval($impuesto->porcentaje);
                $this->totalesFactura['id_cuenta_iva'] = $cuentaIva->id;
            }

            $iva = (($totalPorCantidad - $descuento) * ($this->totalesFactura['porcentaje_iva'] / 100));
            if ($ivaIncluido) {
                $iva = round(($totalPorCantidad - $descuento) - (($totalPorCantidad - $descuento) / (1 + ($this->totalesFactura['porcentaje_iva'] / 100))), 2);
            }
        }

        if ($ivaIncluido && array_key_exists('porcentaje_iva', $this->totalesFactura)) {
            $costo = round((float)$costo / (1 + ($this->totalesFactura['porcentaje_iva'] / 100)), 2);
        }

        $subtotal = ($totalProductos * $costo) - $descuento;
        $this->totalesFactura['subtotal']+= $subtotal;
        $this->totalesFactura['total_iva']+= $iva;
        $this->totalesFactura['total_descuento']+= $descuento;
        $this->totalesFactura['total_factura']+= $subtotal + $iva;

        if ($sumarCuartoHora) {
            $this->totalesFactura['total_factura']+= ($costo / 4);
        }

        if ($this->totalesFactura['total_factura'] >= $this->totalesFactura['tope_retencion'] && $this->totalesFactura['porcentaje_rete_fuente'] > 0) {
            $total_rete_fuente = $ivaIncluido ? $this->totalesFactura['total_factura'] * ($this->totalesFactura['porcentaje_rete_fuente'] / 100) : $this->totalesFactura['subtotal'] * ($this->totalesFactura['porcentaje_rete_fuente'] / 100);
            $this->totalesFactura['total_rete_fuente'] = round($total_rete_fuente);
            $this->totalesFactura['total_factura'] = round($this->totalesFactura['total_factura'] - $total_rete_fuente, 1);
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

    private function findFormaPago ($id_forma_pago)
    {
        return FacFormasPago::where('id', $id_forma_pago)
            ->with(
                'cuenta'
            )
            ->first();
    }

}
