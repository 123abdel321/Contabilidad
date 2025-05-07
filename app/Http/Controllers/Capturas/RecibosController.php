<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Config;
use Carbon\Carbon;
use DateTimeImmutable;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\Printers\RecibosPdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConRecibos;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\CentroCostos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\ConReciboPagos;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\ConReciboDetalles;
use App\Models\Sistema\ArchivosGenerales;

class RecibosController extends Controller
{
    use BegConsecutiveTrait;
    use BegDocumentHelpersTrait;

    protected $id_recibo = 0;
    protected $messages = null;
    protected $fechaManual = null;
    protected $totalesFactura = [
        'total_abonado' => 0,
        'total_anticipo' => 0,
        'total_pagado' => 0
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
            'comprobantes' => Comprobantes::where('tipo_comprobante', Comprobantes::TIPO_INGRESOS)->get()
        ];

        return view('pages.capturas.recibo.recibo-view', $data);
    }

    public function generate(Request $request)
    {
        $editarRecibos = auth()->user()->can("recibo update");
        $idNit = $request->get('id_nit');
        $consecutivo = $request->get('consecutivo');
        $idComprobante = $request->get('id_comprobante');

        if (!$idNit && !$idComprobante) {
			return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Recibo generado con exito!'
            ], Response::HTTP_OK);
		}

        $reciboEdit = null;
        $fechaManual = request()->user()->can('recibo fecha') ? $request->get('fecha_manual', null) : Carbon::now();
        
        try {

            if ($idComprobante && $consecutivo && $editarRecibos) {

                $reciboEdit = ConRecibos::with('detalles', 'pagos', 'nit')
                    ->where('id_comprobante', $idComprobante)
                    ->where('consecutivo', $consecutivo)
                    ->first();

                if ($reciboEdit) {
                    $reciboEdit = $reciboEdit->toArray();
                    $idNit = $reciboEdit['id_nit'];
                    $fechaManual = $reciboEdit['fecha_manual'];
                }
            }

            if (!$idNit && !$reciboEdit) {
                return response()->json([
                    'success'=>	true,
                    'data' => [],
                    'message'=> 'Recibo generado con exito!'
                ], Response::HTTP_OK);
            }
            
            $extractos = (new Extracto(
                $idNit,
                3,
                null,
                $fechaManual,
                // $reciboEdit ? $consecutivo : null
            ))->actual()->get();
            
            if (!count($extractos) && !$idNit && !$reciboEdit) {
                return response()->json([
                    'success'=>	true,
                    'data' => [],
                    'message'=> 'Recibo generado con exito!'
                ], Response::HTTP_OK);
            }
            
            if ($reciboEdit) {
                $detalles = $reciboEdit['detalles'];
                
                if (count($extractos)) {
                    foreach ($extractos as $key => $extracto) {
                        $indice = array_search($extracto->documento_referencia, array_column($detalles, 'documento_referencia'));
                        
                        if (($indice || $indice == 0) && array_key_exists($indice, $detalles)) {
                            $encontrado = $detalles[$indice];
                            $extractos[$key] = $this->formatExtractoEdit($extracto, $encontrado);
                            // dd($extractos);
                            unset($detalles[$indice]);
                        }
                    }
                }
                
                if (count($detalles)) {
                    foreach ($detalles as $detalle) {
                        $extractos[] = $this->addExtractoData($detalle);
                    }
                }
            }

            if ($request->get('orden_cuentas')) {

                $ordenFacturacion = $request->get('orden_cuentas');
                asort($ordenFacturacion);

                $extractos = $extractos->sortBy(function ($item) use ($ordenFacturacion) {
                    return $ordenFacturacion[$item->id_cuenta] ?? 9999;
                })->values();
            } else {
                $extractos = $extractos->sortBy('cuenta')->values();
            }

            $cxcAnticipos = PlanCuentas::where('auxiliar', 1)
                ->where('exige_documento_referencia', 1)
                ->whereHas('tipos_cuenta', function ($query) {
                    $query->whereIn('id_tipo_cuenta', [8]);
                })
                ->orderBy('cuenta', 'ASC')
                ->get();
            
            $dataRecibos = [];

            if (count($extractos)) {
                foreach ($extractos as $extracto) {
                    $dataRecibos[] = $this->formatExtracto($extracto);
                }
            } else {
                $this->id_recibo++;
                $dataRecibos[] = [
                    'id' => $this->id_recibo,
                    'id_cuenta' => '',
                    'codigo_cuenta' => '',
                    'nombre_cuenta' => 'SIN CUENTAS POR COBRAR',
                    'fecha_manual' => '',
                    'dias_cumplidos' => '',
                    'plazo' => '',
                    'documento_referencia' => '',
                    'saldo' => '',
                    'valor_recibido' => '',
                    'nuevo_saldo' => '',
                    'total_abono' => '',
                    'concepto' => '',
                    'cuenta_recibo' => 'sin_deuda',
                ];
            }

            foreach ($cxcAnticipos as $cxcAnticipo) {
                $dataRecibos[] = $this->formatCuentaAnticipo($cxcAnticipo, $request->get('id_nit'));
            }

            return response()->json([
                'success'=>	true,
                'data' => $dataRecibos,
                'edit' => $reciboEdit,
                'message'=> 'Recibo generado con exito!'
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

    public function generateComprobante(Request $request)
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

        $recibos = ConRecibos::orderBy('id','DESC')
            ->with('nit', 'archivos', 'pagos')
            ->where('total_abono', '>', 0)
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        if ($request->get('estado') || $request->get('estado') == 0) {
            if ($request->get('estado') != 'todos') {
                $recibos->where('estado', $request->get('estado'));
            }
        }

        if ($request->get('search')) {
            $recibos->orWhereHas('nit', function ($query) use ($request){
                $query->orWhere('primer_apellido', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('segundo_apellido', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('primer_nombre', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('otros_nombres', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('razon_social', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('email', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('numero_documento', 'LIKE', '%' . $request->get("search") . '%');
            });
        }

        $recibos ->when($request->get('fecha_desde') && $request->get('fecha_hasta'), function ($query) use($request) {
                $query->whereBetween('fecha_manual', [$request->get('fecha_desde'), $request->get('fecha_hasta')]);
            })
            ->where('total_abono', '>', 0);

        $recibosTotals = $recibos->get();

        $recibosPaginate = $recibos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $recibosTotals->count(),
            'iTotalDisplayRecords' => $recibosTotals->count(),
            'data' => $recibosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Recibos comprobantes generados con exito!'
        ]);
    }

    public function create (Request $request)
    {

        $rules = [
            'id_nit' => 'required|exists:sam.nits,id',
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'fecha_manual' => 'required|date',
            'consecutivo' => 'required',
            'file_comprobante' => 'nullable',
            'movimiento' => 'array|required',
            'movimiento.*.id_cuenta' => 'required|exists:sam.plan_cuentas,id',
            'movimiento.*.valor_recibido' => 'required',
            'pagos' => 'array|required',
            'pagos.*.id' => 'required|exists:sam.fac_formas_pagos,id',
            'pagos.*.valor' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $comprobanteRecibo = Comprobantes::where('id', $request->get('id_comprobante'))->first();

        $this->fechaManual = request()->user()->can('recibo fecha') ? $request->get('fecha_manual') : Carbon::now();

        if(!$comprobanteRecibo) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> ['Comprobante recibo' => ['El Comprobante del recibo es incorrecto!']]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $isFechaCierreLimit = $this->isFechaCierreLimit($request->get('fecha_manual'));

        if ($isFechaCierreLimit) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['fecha_manual' => ['mensaje' => 'Se esta grabando en un año cerrado']]
            ], 200);
		}

        if ($request->get('id_recibo')) {
            $recibo = ConRecibos::where('id', $request->get('id_recibo'))->first();

            $consecutivoUsado = $this->consecutivoUsado(
                $comprobanteRecibo,
                $request->get('consecutivo'),
                $request->get('fecha_manual'),
                $recibo
            );

            if ($consecutivoUsado) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> "El consecutivo {$request->get('consecutivo')} ya está en uso."
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            if ($recibo) {
                $recibo->documentos()->delete();
                $recibo->detalles()->delete();
                $recibo->pagos()->delete();
                $recibo->delete();
            }
        } else {
            $consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $this->fechaManual);
            $request->request->add(['consecutivo' => $consecutivo]);
        }

        $empresa = Empresa::where('id', request()->user()->id_empresa)->first();
		$fechaCierre= DateTimeImmutable::createFromFormat('Y-m-d', $empresa->fecha_ultimo_cierre);

        if (!$request->get('fecha_manual')) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['fecha_manual' => ['mensaje' => 'La fecha es incorrecta']]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
		}
        
        try {
            DB::connection('sam')->beginTransaction();
            //CREAR FACTURA RECIBO
            $recibo = $this->createFacturaRecibo($request);
            $nit = $this->findNit($recibo->id_nit);
            $centro_costos = CentroCostos::first();

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE RECIBOS
            $documentoGeneral = new Documento(
                $request->get('id_comprobante'),
                $recibo,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            foreach ($request->get('movimiento') as $movimiento) {
                $movimiento = (object)$movimiento;
                $cuentaRecord = PlanCuentas::find($movimiento->id_cuenta);

                //CREAR RECIBO DETALLE
                ConReciboDetalles::create([
                    'id_recibo' => $recibo->id,
                    'id_cuenta' => $cuentaRecord->id,
                    'id_nit' => $recibo->id_nit,
                    'fecha_manual' => $recibo->fecha_manual,
                    'documento_referencia' => $movimiento->documento_referencia ? $movimiento->documento_referencia : $recibo->consecutivo,
                    'consecutivo' => $recibo->consecutivo,
                    'concepto' => $movimiento->concepto,
                    'total_factura' => 0,
                    'total_abono' => $movimiento->cuenta_recibo ? $movimiento->valor_recibido : 0,
                    'total_saldo' => $movimiento->cuenta_recibo ? $movimiento->saldo : 0,
                    'nuevo_saldo' => $movimiento->cuenta_recibo ? $movimiento->saldo - $movimiento->valor_recibido : 0,
                    'total_anticipo' => $movimiento->cuenta_recibo ? 0 : $movimiento->valor_recibido,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                //AGREGAR MOVIMIENTO CONTABLE
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRecord->id,
                    "id_nit" => $cuentaRecord->exige_nit ? $recibo->id_nit : null,
                    "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $centro_costos->id : null,
                    "concepto" => $cuentaRecord->exige_concepto ? $movimiento->concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $movimiento->documento_referencia : null,
                    "debito" => $movimiento->valor_recibido,
                    "credito" => $movimiento->valor_recibido,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_ingresos);
            }

            $totalRecibos = $this->totalesFactura['total_pagado'];

            //AGREGAR FORMAS DE PAGO
            foreach ($request->get('pagos') as $pago) {
                $pago = (object)$pago;
                $totalRecibos-= $pago->valor;
                $formaPago = $this->findFormaPago($pago->id);
                $documentoReferenciaAnticipos = $this->isAnticiposDocumentoRefe($formaPago, $nit->id);

                ConReciboPagos::create([
                    'id_recibo' => $recibo->id,
                    'id_forma_pago' => $pago->id,
                    'valor' => $pago->valor,
                    'saldo' => $totalRecibos,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                $doc = new DocumentosGeneral([
                    'id_cuenta' => $formaPago->cuenta->id,
                    'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
                    'id_centro_costos' => null,
                    'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL RECIBO: '.$nit->nombre_nit.' - '.$recibo->consecutivo : null,
                    'documento_referencia' => $documentoReferenciaAnticipos,
                    'debito' => $pago->valor,
                    'credito' => $pago->valor,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);
            }

            if (!$request->get('id_recibo')) {
                $this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));
            }

            if (!$documentoGeneral->save()) {

				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], Response::HTTP_UNPROCESSABLE_ENTITY);
			}

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $documentoGeneral->getRows(),
                'impresion' => $comprobanteRecibo->imprimir_en_capturas ? $recibo->id : '',
                'message'=> 'Recibo creado con exito!'
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

    public function createComprobante(Request $request)
    {
        $comprobanteRecibo = Comprobantes::where('id', $request->get('id_comprobante'))->first();

        $this->fechaManual = request()->user()->can('recibo fecha') ? $request->get('fecha_pago', null) : Carbon::now();

        if(!$comprobanteRecibo) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> ['Comprobante recibo' => ['El Comprobante del recibo es incorrecto!']]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            // $consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $this->fechaManual);
            // $request->request->add([
            //     'consecutivo' => $consecutivo
            // ]);
        }

        $rules = [
            'id_nit' => 'required|exists:sam.nits,id',
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'numero_documento' => 'required|exists:sam.nits,numero_documento',
            'fecha_pago' => 'nullable',
            'valor_comprobante' => 'nullable',
            'valor_pago' => 'nullable',
            'comprobante' => 'nullable',
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
            //CREAR FACTURA RECIBO
            $recibo = $this->createReciboComprobante($request);
            $nit = $this->findNit($recibo->id_nit);
            $formaPago = $this->findFormaPagoCuenta($request->get('id_cuenta_ingreso'));

            //AGREGAR MOVIMIENTO PAGO
            if (!$formaPago) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'La forma de pago con el id_cuenta_ingreso: '.$request->get('id_cuenta_ingreso').' No existe!'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $valorPago = $request->get('valor_pago') ? $request->get('valor_pago') : $request->get('valor_comprobante');

            //GUARDAR FORMA DE PAGO
            $pagoRecibo = ConReciboPagos::create([
                'id_recibo' => $recibo->id,
                'id_forma_pago' => $formaPago->id,
                'valor' => $valorPago,
                'saldo' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            if ($request->get('valor_comprobante')) {
                DB::connection('sam')->commit();
                // //ACTIVAR SOLO COMPROBANTES
                return response()->json([
                    "success"=>true,
                    'data' => [],
                    "message"=>'Comprobante enviado con exito'
                ], Response::HTTP_OK);
            }

            $consecutivo = $this->getNextConsecutive($recibo->id_comprobante, $recibo->fecha_manual);

            $extractos = (new Extracto(
                $nit->id,
                3,
                null,
                $request->get('fecha_pago')
            ))->actual()->get();

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE RECIBOS
            $documentoGeneral = new Documento(
                $request->get('id_comprobante'),
                $recibo,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            $valorPagado = $request->get('valor_pago');
            $centro_costos = CentroCostos::first();
            
            //BUSCAMOS CUENTAS POR COBRAR
            foreach ($extractos as $extracto) {
                if (!$valorPagado) continue;
                
                $cuentaRecord = PlanCuentas::find($extracto->id_cuenta);
                $totalAbonado = 0;
                if ($extracto->saldo >= $valorPagado) {
                    $totalAbonado = $valorPagado;
                    $valorPagado = 0;
                } else {
                    $totalAbonado = $extracto->saldo;
                    $valorPagado-= $extracto->saldo;
                }
                //CREAR RECIBO DETALLE
                ConReciboDetalles::create([
                    'id_recibo' => $recibo->id,
                    'id_cuenta' => $cuentaRecord->id,
                    'id_nit' => $recibo->id_nit,
                    'fecha_manual' => $recibo->fecha_manual,
                    'documento_referencia' => $extracto->documento_referencia,
                    'consecutivo' => $recibo->consecutivo,
                    'concepto' => 'PAGO PASARELA',
                    'total_factura' => 0,
                    'total_abono' => $totalAbonado,
                    'total_saldo' => $extracto->saldo,
                    'nuevo_saldo' => $extracto->saldo - $totalAbonado,
                    'total_anticipo' => 0,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                //AGREGAR MOVIMIENTO CONTABLE
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRecord->id,
                    "id_nit" => $cuentaRecord->exige_nit ? $recibo->id_nit : null,
                    "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $centro_costos->id : null,
                    "concepto" => $cuentaRecord->exige_concepto ? $extracto->concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $extracto->documento_referencia : null,
                    "debito" => $totalAbonado,
                    "credito" => $totalAbonado,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                
                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_ingresos);
            }

            //AGREGAR MOVIMIENTO CONTABLE PAGO
            $doc = new DocumentosGeneral([
                'id_cuenta' => $formaPago->cuenta->id,
                'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
                'id_centro_costos' => null,
                'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL PAGO: '.$nit->nombre_nit.' - '.$recibo->consecutivo : null,
                'documento_referencia' => null,
                'debito' => $request->get('valor_pago'),
                'credito' => $request->get('valor_pago'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);

            $this->updateConsecutivo($recibo->id_comprobante, $consecutivo);

            if (!$documentoGeneral->save()) {

				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], Response::HTTP_UNPROCESSABLE_ENTITY);
			}

            //GUARDAMOS RECIBO
            $recibo->consecutivo = $consecutivo;
            $recibo->estado = 1;
            $recibo->observacion = $request->get('observacion');
            $recibo->save();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $documentoGeneral->getRows(),
                'impresion' => $comprobanteRecibo->imprimir_en_capturas ? $recibo->id : '',
                'message'=> 'Recibo creado con exito!'
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

    public function updateComprobante(Request $request)
    {
        if (!$request->has('estado')) {
            $rules = [
                'id' => 'required|exists:sam.con_recibos,id',
                'fecha_pago' => 'required',
                'valor_comprobante' => 'required',
                'comprobante' => 'nullable',
            ];
    
            $validator = Validator::make($request->all(), $rules, $this->messages);
    
            if ($validator->fails()){
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>$validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        try {
            DB::connection('sam')->beginTransaction();

            //ACTUALIZAR VALOR COMPROBANTE
            if ($request->has('valor_comprobante')) {
                ConRecibos::where('id', $request->get('id'))
                    ->where('estado', 2)
                    ->update([
                        'total_abono' => $request->get('valor_comprobante')
                    ]);
    
                ConReciboPagos::where('id_recibo', $request->get('id'))
                    ->update([
                        'valor' => $request->get('valor_comprobante')
                    ]);
            }

            //ACTUALIZAR ESTADO COMPROBANTE
            if (!$request->has('estado')) {
                DB::connection('sam')->commit();

                return response()->json([
                    'success'=>	true,
                    'data' => [],
                    'message'=> 'Recibo actualizado con exito!'
                ], Response::HTTP_OK);
            }

            if ($request->get('estado') == 0) {

                ConRecibos::where('id', $request->get('id'))
                    ->where('estado', 2)
                    ->update([
                        'estado' => 0,
                        'observacion' => $request->get('observacion')
                    ]);

                DB::connection('sam')->commit();

                return response()->json([
                    'success'=>	true,
                    'data' => [],
                    'message'=> 'Recibo actualizado con exito!'
                ], Response::HTTP_OK);
            }

            $recibo = ConRecibos::where('id', $request->get('id'))
                ->with('pagos')
                ->where('estado', 2)
                ->first();

            if (!$recibo) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'El recibo no se puede modificar'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $nit = $this->findNit($recibo->id_nit);

            //GENERAR MOVIMINETO CONTABLE
            $centro_costos = CentroCostos::first();
            $consecutivo = $this->getNextConsecutive($recibo->id_comprobante, $recibo->fecha_manual);
            $recibo->consecutivo = $consecutivo;

            $documentoGeneral = new Documento(
                $recibo->id_comprobante,
                $recibo,
                $recibo->fecha_manual,
                $consecutivo
            );

            //BUSCAMOS CUENTAS POR COBRAR
            $extractos = (new Extracto(
                $recibo->id_nit,
                3,
                null,
                $recibo->fecha_manual
            ))->actual()->get();

            if (!count($extractos)) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'El nit no tiene cuentas por cobrar'
                ], Response::HTTP_UNPROCESSABLE_ENTITY); 
            }

            $valorPagado = $recibo->total_abono;

            foreach ($extractos as $extracto) {
                if (!$valorPagado) continue;

                $cuentaRecord = PlanCuentas::find($extracto->id_cuenta);
                $totalAbonado = 0;
                if ($extracto->saldo >= $valorPagado) {
                    $totalAbonado = $valorPagado;
                    $valorPagado = 0;
                } else {
                    $totalAbonado = $extracto->saldo;
                    $valorPagado-= $extracto->saldo;
                }
                

                //AGREGAR MOVIMIENTO CONTABLE
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRecord->id,
                    "id_nit" => $cuentaRecord->exige_nit ? $recibo->id_nit : null,
                    "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $centro_costos->id : null,
                    "concepto" => $cuentaRecord->exige_concepto ? $extracto->concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $extracto->documento_referencia : null,
                    "debito" => $totalAbonado,
                    "credito" => $totalAbonado,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                
                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_ingresos);
            }

            //GUARDAMOS EL VALOR RESTANTE COMO ANTICIPO
            if ($valorPagado) {

                $formaPagoAnticipos = FacFormasPago::with('cuenta')
                    ->whereHas('cuenta', function ($query) {
                    $query->whereHas('tipos_cuenta', function ($q) {
                        $q->whereIn('id_tipo_cuenta', [4,8]);
                    });
                })->first();

                $comprobante = Comprobantes::find($recibo->id_comprobante);
                
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $formaPagoAnticipos->cuenta->id,
                    "id_nit" => $formaPagoAnticipos->cuenta->exige_nit ? $recibo->id_nit : null,
                    "id_centro_costos" => null,
                    "concepto" => $formaPagoAnticipos->cuenta->exige_concepto ? 'ANTICIPOS COMPROBANTE: '.$extracto->concepto : null,
                    "documento_referencia" => $formaPagoAnticipos->cuenta->exige_documento_referencia ? $consecutivo : null,
                    "debito" => $valorPagado,
                    "credito" => $valorPagado,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);

                $documentoGeneral->addRow($doc, $formaPagoAnticipos->cuenta->naturaleza_ingresos);
            }

            //AGREGAR MOVIMIENTO CONTABLE PAGO
            $reciboPagos = $recibo->pagos[0];
            $formaPago = $this->findFormaPago($reciboPagos->id_forma_pago);
            
            $doc = new DocumentosGeneral([
                'id_cuenta' => $formaPago->cuenta->id,
                'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
                'id_centro_costos' => null,
                'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL PAGO COMPROBANTE: '.$nit->nombre_nit.' - '.$consecutivo : null,
                'documento_referencia' => null,
                'debito' => $reciboPagos->valor,
                'credito' => $reciboPagos->valor,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ingresos);

            $this->updateConsecutivo($recibo->id_comprobante, $consecutivo);

            if (!$documentoGeneral->save()) {

				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], Response::HTTP_UNPROCESSABLE_ENTITY);
			}

            //GUARDAMOS RECIBO
            $recibo->consecutivo = $consecutivo;
            $recibo->estado = 1;
            $recibo->observacion = $request->get('observacion');
            $recibo->save();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $documentoGeneral->getRows(),
                'message'=> 'Recibo aprobado con exito!'
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

    public function deleteComprobante(Request $request)
    {
        $rules = [
            'id' => 'required|exists:sam.con_recibos,id',
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

            ConRecibos::where('id', $request->get('id'))
                ->where('estado', 2)
                ->delete();

            ConReciboPagos::where('id_recibo', $request->get('id'))
                ->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Inmueble eliminada con exito!'
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
        $recibo = ConRecibos::whereId($id)
            ->with('comprobante')
            ->first();

        if(!$recibo) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'El recibo no existe'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        $data = (new RecibosPdf($empresa, $recibo))->buildPdf()->getData();
 
        return (new RecibosPdf($empresa, $recibo))
            ->buildPdf()
            ->showPdf();
    }

    public function showPdfPublic(Request $request)
    {
        $token_db = base64_decode($request->get('token_db'));
        $empresa = Empresa::where('token_db', $token_db)->first();

		Config::set('database.connections.sam.database', $token_db);
        
        $recibo = ConRecibos::whereId($request->get('id'))
            ->with('comprobante')
            ->first();

        if(!$recibo) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'El recibo no existe'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = (new RecibosPdf($empresa, $recibo))->buildPdf()->getData();
 
        return (new RecibosPdf($empresa, $recibo))
            ->buildPdf()
            ->showPdf();
    }

    private function formatExtracto($extracto)
    {
        $editando = property_exists($extracto, 'edit') ? true : false;
        
        $this->id_recibo++;
        return [
            'id' => $this->id_recibo,
            'id_cuenta' =>  $extracto->id_cuenta,
            'codigo_cuenta' => $extracto->cuenta,
            'nombre_cuenta' => $extracto->nombre_cuenta,
            'fecha_manual' => $extracto->fecha_manual,
            'dias_cumplidos' => $extracto->dias_cumplidos,
            'plazo' => $extracto->plazo,
            'documento_referencia' => $extracto->documento_referencia,
            'saldo' => $editando ? $extracto->total_saldo : $extracto->saldo,
            'valor_recibido' => $editando ? $extracto->total_abono : 0,
            'nuevo_saldo' => $extracto->saldo,
            'total_abono' => $extracto->total_abono,
            'concepto' => $editando ? $extracto->concepto : '',
            'cuenta_recibo' => true,
        ];
    }

    private function formatExtractoEdit($extracto, $recibo)
    {
        $extracto->total_abono = $recibo['total_abono'];
        $extracto->total_saldo = $extracto->saldo;
        $extracto->concepto = $recibo['concepto'];
        $extracto->nuevo_saldo = $recibo['nuevo_saldo'];
        $extracto->valor_recibido = $recibo['total_abono'];
        $extracto->edit = true;
        
        return $extracto;
    }

    private function addExtractoData($detalle)
    {
        $detalle = (object)$detalle;
        $cuenta = PlanCuentas::find($detalle->id_cuenta);
        return (object)[
            'id_cuenta' => $detalle->id_cuenta,
            'cuenta' => $cuenta->cuenta,
            'nombre_cuenta' => $cuenta->nombre,
            'fecha_manual' => $detalle->fecha_manual,
            'concepto' => $detalle->concepto,
            'dias_cumplidos' => 0,
            'plazo' => 0,
            'documento_referencia' => $detalle->documento_referencia,
            'total_abono' => $detalle->total_abono,
            'total_saldo' => $detalle->total_saldo,
            'saldo' => $detalle->nuevo_saldo,
            'edit' => true
        ];
    }

    private function formatCuentaAnticipo($cuenta, $idNit)
    {
        $this->id_recibo++;
        $anticipoCuenta = (new Extracto(
            $idNit,
            null,
            null,
            Carbon::now()->format('Y-m-d H:i:s'),
            $cuenta->id
        ))->anticipos()->first();

        return [
            'id' => $this->id_recibo,
            'id_cuenta' =>  $cuenta->id,
            'codigo_cuenta' => $cuenta->cuenta,
            'nombre_cuenta' => $cuenta->nombre,
            'fecha_manual' => Carbon::now()->format('Y-m-d'),
            'dias_cumplidos' => '',
            'plazo' => '',
            'documento_referencia' => '',
            'saldo' => 0,
            'valor_recibido' => 0,
            'nuevo_saldo' => 0,
            'total_abono' => 0,
            'concepto' => '',
            'cuenta_recibo' => false,
        ];
    }

    private function createFacturaRecibo($request)
    {
        $this->calcularTotales($request->get('movimiento'));
        $this->calcularFormasPago($request->get('pagos')); 

        $recibo = ConRecibos::create([
            'id_nit' => $request->get('id_nit'),
            'id_comprobante' => $request->get('id_comprobante'),
            'fecha_manual' => $this->fechaManual,
            'consecutivo' => $request->get('consecutivo'),
            'total_abono' => $this->totalesFactura['total_abonado'],
            'total_anticipo' => $this->totalesFactura['total_anticipo'],
            'observacion' => '',
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        return $recibo;
    }

    private function createReciboComprobante($request)
    {
        $valorTotal = $request->get('valor_comprobante') ? $request->get('valor_comprobante') : $request->get('valor_pago');

        $recibo = ConRecibos::create([
            'id_nit' => $request->get('id_nit'),
            'id_comprobante' => $request->get('id_comprobante'),
            'fecha_manual' => $request->get('fecha_pago'),
            'consecutivo' => 0,
            'total_abono' => $valorTotal,
            'total_anticipo' => 0,
            'observacion' => '',
            'estado' => $request->get('valor_comprobante') ? 2 : 1,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        if($request->comprobante) {
            $image = $request->comprobante;
            $empresaId = request()->user()->id_empresa;
            $ext = explode(";", explode("/",explode(",", $image)[0])[1])[0];
            $image = str_replace('data:image/'.$ext.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'comprobante_'.uniqid().'.'. $ext;

            $urlImagen = "imagen/{$empresaId}/recibos_pagos/{$imageName}";
            
            Storage::disk('do_spaces')->put($urlImagen, base64_decode($image), 'public');

            $archivo = new ArchivosGenerales([
                'tipo_archivo' => 'imagen',
                'url_archivo' => $urlImagen,
                'estado' => 1,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $archivo->relation()->associate($recibo);
            $recibo->archivos()->save($archivo);
        }

        return $recibo;
    }

    private function calcularTotales ($movimientos)
    {
        foreach ($movimientos as $movimiento) {
            $movimiento = (object)$movimiento;
            if ($movimiento->cuenta_recibo) {
                $this->totalesFactura['total_abonado']+= floatval($movimiento->valor_recibido);
            } else {
                $this->totalesFactura['total_anticipo']+= floatval($movimiento->valor_recibido);
            }
        }
    }

    private function calcularFormasPago($pagos)
    {
        foreach ($pagos as $pago) {
            $pago = (object)$pago;
            $this->totalesFactura['total_pagado']+= floatval($pago->valor);
        }
    }

    private function findNit ($id_nit)
    {
        return Nits::whereId($id_nit)
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
                'cuenta.tipos_cuenta'
            )
            ->first();
    }

    private function findFormaPagoCuenta ($idCuenta)
    {
        return FacFormasPago::where('id_cuenta', $idCuenta)
        ->with(
            'cuenta.tipos_cuenta'
        )
        ->first();
    }

    private function isAnticiposDocumentoRefe($formaPago, $idNit)
    {
        $tiposCuenta = $formaPago->cuenta->tipos_cuenta;
        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == 4 || $tipoCuenta->id_tipo_cuenta == 8) {
                $anticipoCuenta = (new Extracto(
                    $idNit,
                    null,
                    null,
                    Carbon::now()->format('Y-m-d H:i:s'),
                    $formaPago->cuenta->id
                ))->anticipos()->first();
                return $anticipoCuenta ? $anticipoCuenta->documento_referencia : null;
            }
        }
        return null;
    }
}
