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
use App\Helpers\Printers\PagosPdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConPagos;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\CentroCostos;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\ConPagoPagos;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\ConPagoDetalles;
use App\Models\Sistema\ArchivosGenerales;

class PagosController extends Controller
{
    use BegConsecutiveTrait;
    use BegDocumentHelpersTrait;

    protected $id_pago = 0;
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
            'comprobantes' => Comprobantes::where('tipo_comprobante', Comprobantes::TIPO_EGRESOS)->get()
        ];

        return view('pages.capturas.pago.pago-view', $data);
    }

    public function generate(Request $request)
    {
        $editarPagos = auth()->user()->can("pago update");
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

        $pagoEdit = null;
        $fechaManual = request()->user()->can('pago fecha') ? $request->get('fecha_manual', null) : Carbon::now();
        
        try {
            $comprobantePago = Comprobantes::where('id', $request->get('id_comprobante'))->first();

            if ($idComprobante && $consecutivo && $editarPagos) {

                $pagoEdit = ConPagos::with('detalles', 'pagos', 'nit')
                    ->where('id_comprobante', $idComprobante)
                    ->where('consecutivo', $consecutivo);

                if ($comprobantePago->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
                    $this->filterCapturaMensual($pagoEdit, $request->get('fecha_manual'));
                }

                $pagoEdit = $pagoEdit->first();

                if ($pagoEdit) {
                    $pagoEdit = $pagoEdit->toArray();
                    $idNit = $pagoEdit['id_nit'];
                    $fechaManual = $pagoEdit['fecha_manual'];
                }
            }

            if (!$idNit && !$pagoEdit) {
                return response()->json([
                    'success'=>	true,
                    'data' => [],
                    'message'=> 'Recibo generado con exito!'
                ], Response::HTTP_OK);
            }

            $extractos = (new Extracto(
                $idNit,
                [4,8],
                null,
                $fechaManual
            ))->actual()->get();

            if (!count($extractos) && !$idNit && !$pagoEdit) {
                return response()->json([
                    'success'=>	true,
                    'data' => [],
                    'message'=> 'Pago generado con exito!'
                ], Response::HTTP_OK);
            }

            if ($pagoEdit) {
                $detalles = $pagoEdit['detalles'];
                
                if (count($extractos)) {
                    foreach ($extractos as $key => $extracto) {
                        $indice = array_search($extracto->documento_referencia, array_column($detalles, 'documento_referencia'));
                        
                        if (($indice || $indice == 0) && array_key_exists($indice, $detalles)) {
                            $encontrado = $detalles[$indice];
                            $extractos[$key] = $this->formatExtractoEdit($extracto, $encontrado);
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

            $cxpAnticipos = PlanCuentas::where('auxiliar', 1)
                ->where('exige_documento_referencia', 1)
                ->whereHas('tipos_cuenta', function ($query) {
                    $query->whereIn('id_tipo_cuenta', [3,7]);
                })
                ->orderBy('cuenta', 'ASC')
                ->get();
            
            $dataPagos = [];

            if (count($extractos)) {
                foreach ($extractos as $extracto) {
                    $dataPagos[] = $this->formatExtracto($extracto);
                }
            } else {
                $this->id_pago++;
                $dataPagos[] = [
                    'id' => $this->id_pago,
                    'id_cuenta' => '',
                    'codigo_cuenta' => '',
                    'nombre_cuenta' => 'SIN CUENTAS POR PAGAR',
                    'fecha_manual' => '',
                    'dias_cumplidos' => '',
                    'plazo' => '',
                    'documento_referencia' => '',
                    'saldo' => '',
                    'valor_recibido' => '',
                    'nuevo_saldo' => '',
                    'total_abono' => '',
                    'concepto' => '',
                    'cuenta_pago' => 'sin_deuda',
                ];
            }

            foreach ($cxpAnticipos as $cxcAnticipo) {
                $dataPagos[] = $this->formatCuentaAnticipo($cxcAnticipo, $request->get('id_nit'));
            }

            return response()->json([
                'success'=>	true,
                'data' => $dataPagos,
                'edit' => $pagoEdit,
                'message'=> 'Pago generado con exito!'
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

        $pagos = ConPagos::orderBy('id','DESC')
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
                $pagos->where('estado', $request->get('estado'));
            }
        }

        if ($request->get('search')) {
            $pagos->orWhereHas('nit', function ($query) use ($request){
                $query->orWhere('primer_apellido', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('segundo_apellido', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('primer_nombre', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('otros_nombres', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('razon_social', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('email', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('numero_documento', 'LIKE', '%' . $request->get("search") . '%');
            });
        }

        $pagos ->when($request->get('fecha_desde') && $request->get('fecha_hasta'), function ($query) use($request) {
                $query->whereBetween('fecha_manual', [$request->get('fecha_desde'), $request->get('fecha_hasta')]);
            })
            ->where('total_abono', '>', 0);

        $pagosTotals = $pagos->get();

        $pagosPaginate = $pagos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $pagosTotals->count(),
            'iTotalDisplayRecords' => $pagosTotals->count(),
            'data' => $pagosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Pagos comprobantes generados con exito!'
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

        $comprobantePago = Comprobantes::where('id', $request->get('id_comprobante'))->first();

        $this->fechaManual = request()->user()->can('pago fecha') ? $request->get('fecha_manual') : Carbon::now();

        if(!$comprobantePago) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> ['Comprobante pago' => ['El Comprobante del pago es incorrecto!']]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } else if (!$request->get('id_pago')){

            $consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $this->fechaManual);
            $request->request->add([
                'consecutivo' => $consecutivo
            ]);
        }

        $isFechaCierreLimit = $this->isFechaCierreLimit($request->get('fecha_manual'));

        if ($isFechaCierreLimit) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['fecha_manual' => ['mensaje' => 'Se esta grabando en un año cerrado']]
            ], 200);
		}

        if ($request->get('id_pago')) {
            $pago = ConPagos::where('id', $request->get('id_pago'))->first();

            $consecutivoUsado = $this->consecutivoUsado(
                $comprobantePago,
                $request->get('consecutivo'),
                $request->get('fecha_manual'),
                $pago,
            );

            if ($consecutivoUsado) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> "El consecutivo {$request->get('consecutivo')} ya está en uso."
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            if ($pago) {
                $pago->documentos()->delete();
                $pago->detalles()->delete();
                $pago->pagos()->delete();
                $pago->delete();
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
            $pago = $this->createFacturaPago($request);
            $nit = $this->findNit($pago->id_nit);
            $centro_costos = CentroCostos::first();

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE RECIBOS
            $documentoGeneral = new Documento(
                $request->get('id_comprobante'),
                $pago,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            foreach ($request->get('movimiento') as $movimiento) {
                $movimiento = (object)$movimiento;
                $cuentaRecord = PlanCuentas::find($movimiento->id_cuenta);

                //CREAR RECIBO DETALLE
                ConPagoDetalles::create([
                    'id_pago' => $pago->id,
                    'id_cuenta' => $cuentaRecord->id,
                    'id_nit' => $pago->id_nit,
                    'fecha_manual' => $pago->fecha_manual,
                    'documento_referencia' => $movimiento->documento_referencia ? $movimiento->documento_referencia : $pago->consecutivo,
                    'consecutivo' => $pago->consecutivo,
                    'concepto' => $movimiento->concepto,
                    'total_factura' => 0,
                    'total_abono' => $movimiento->cuenta_pago ? $movimiento->valor_recibido : 0,
                    'total_saldo' => $movimiento->cuenta_pago ? $movimiento->saldo : 0,
                    'nuevo_saldo' => $movimiento->cuenta_pago ? $movimiento->saldo - $movimiento->valor_recibido : 0,
                    'total_anticipo' => $movimiento->cuenta_pago ? 0 : $movimiento->valor_recibido,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                //AGREGAR MOVIMIENTO CONTABLE
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $cuentaRecord->id,
                    "id_nit" => $cuentaRecord->exige_nit ? $pago->id_nit : null,
                    "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $pago->id_centro_costos : null,
                    "concepto" => $cuentaRecord->exige_concepto ? $movimiento->concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $movimiento->documento_referencia : null,
                    "debito" => $movimiento->valor_recibido,
                    "credito" => $movimiento->valor_recibido,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_egresos);
            }

            $totalPagos = $this->totalesFactura['total_pagado'];

            //AGREGAR FORMAS DE PAGO
            foreach ($request->get('pagos') as $pagoItem) {

                $pagoItem = (object)$pagoItem;
                $totalPagos-= $pagoItem->valor;
                $formaPago = $this->findFormaPago($pagoItem->id);
                $documentoReferenciaAnticipos = $this->isAnticiposDocumentoRefe($formaPago, $nit->id);
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
                            $pago,
                            $anticipoUsado,
                            $totalPagos
                        );
                        $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_egresos);
                    }
                } else {
                    $doc = $this->addFormaPago(
                        null,
                        $formaPago,
                        $nit,
                        $pagoItem,
                        $pago,
                        $pagoItem->valor,
                        $totalPagos
                    );
                    $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_egresos);
                }
            }

            if (!$request->get('id_pago')) {
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
                'impresion' => $comprobantePago->imprimir_en_capturas ? $pago->id : '',
                'message'=> 'Pago creado con exito!'
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
        $comprobantePago = Comprobantes::where('id', $request->get('id_comprobante'))->first();

        $this->fechaManual = request()->user()->can('pago fecha') ? $request->get('fecha_pago', null) : Carbon::now();

        if(!$comprobantePago) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> ['Comprobante pago' => ['El Comprobante del pago es incorrecto!']]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $this->fechaManual);
            $request->request->add([
                'consecutivo' => $consecutivo
            ]);
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
            $pago = $this->createPagoComprobante($request);
            $nit = $this->findNit($pago->id_nit);
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
            $pagoPago = ConPagoPagos::create([
                'id_pago' => $pago->id,
                'id_forma_pago' => $formaPago->id,
                'valor' => $valorPago,
                'saldo' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            if ($request->get('valor_comprobante')) {

                DB::connection('sam')->commit();

                return response()->json([
                    "success"=>true,
                    'data' => [],
                    "message"=>'Comprobante enviado con exito'
                ], Response::HTTP_OK);
            }

            $extractos = (new Extracto(
                $nit->id,
                3,
                null,
                $request->get('fecha_pago')
            ))->actual()->get();

            //GUARDAR DETALLE & MOVIMIENTO CONTABLE RECIBOS
            $documentoGeneral = new Documento(
                $request->get('id_comprobante'),
                $pago,
                $request->get('fecha_manual'),
                $request->get('consecutivo')
            );

            $valorPagado = $request->get('valor_pago');
            
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
                ConPagoDetalles::create([
                    'id_pago' => $pago->id,
                    'id_cuenta' => $cuentaRecord->id,
                    'id_nit' => $pago->id_nit,
                    'fecha_manual' => $pago->fecha_manual,
                    'documento_referencia' => $extracto->documento_referencia,
                    'consecutivo' => $pago->consecutivo,
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
                    "id_nit" => $cuentaRecord->exige_nit ? $pago->id_nit : null,
                    "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $pago->id_centro_costos : null,
                    "concepto" => $cuentaRecord->exige_concepto ? $extracto->concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $extracto->documento_referencia : null,
                    "debito" => $totalAbonado,
                    "credito" => $totalAbonado,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                
                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_egresos);
            }

            //AGREGAR MOVIMIENTO CONTABLE PAGO
            $doc = new DocumentosGeneral([
                'id_cuenta' => $formaPago->cuenta->id,
                'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
                'id_centro_costos' => null,
                'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL PAGO: '.$nit->nombre_nit.' - '.$pago->consecutivo : null,
                'documento_referencia' => null,
                'debito' => $request->get('valor_pago'),
                'credito' => $request->get('valor_pago'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);

            $this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));

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
                'impresion' => $comprobantePago->imprimir_en_capturas ? $pago->id : '',
                'message'=> 'Pago creado con exito!'
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
                'id' => 'required|exists:sam.con_pagos,id',
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
                ConPagos::where('id', $request->get('id'))
                    ->where('estado', 2)
                    ->update([
                        'total_abono' => $request->get('valor_comprobante')
                    ]);
    
                ConPagoPagos::where('id_pago', $request->get('id'))
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
                    'message'=> 'Pago actualizado con exito!'
                ], Response::HTTP_OK);
            }

            if ($request->get('estado') == 0) {

                ConPagos::where('id', $request->get('id'))
                    ->where('estado', 2)
                    ->update([
                        'estado' => 0,
                        'observacion' => $request->get('observacion')
                    ]);

                DB::connection('sam')->commit();

                return response()->json([
                    'success'=>	true,
                    'data' => [],
                    'message'=> 'Pago actualizado con exito!'
                ], Response::HTTP_OK);
            }

            $pago = ConPagos::where('id', $request->get('id'))
                ->with('pagos')
                ->where('estado', 2)
                ->first();

            if (!$pago) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'El pago no se puede modificar'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $nit = $this->findNit($pago->id_nit);

            //GENERAR MOVIMINETO CONTABLE
            $consecutivo = $this->getNextConsecutive($pago->id_comprobante, $pago->fecha_manual);
            
            $documentoGeneral = new Documento(
                $pago->id_comprobante,
                $pago,
                $pago->fecha_manual,
                $consecutivo
            );

            //BUSCAMOS CUENTAS POR COBRAR
            $extractos = (new Extracto(
                $pago->id_nit,
                3,
                null,
                $pago->fecha_manual
            ))->actual()->get();

            if (!count($extractos)) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'El nit no tiene cuentas por cobrar'
                ], Response::HTTP_UNPROCESSABLE_ENTITY); 
            }

            $valorPagado = $pago->total_abono;

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
                    "id_nit" => $cuentaRecord->exige_nit ? $pago->id_nit : null,
                    "id_centro_costos" => $cuentaRecord->exige_centro_costos ? $pago->id_centro_costos : null,
                    "concepto" => $cuentaRecord->exige_concepto ? $extracto->concepto : null,
                    "documento_referencia" => $cuentaRecord->exige_documento_referencia ? $extracto->documento_referencia : null,
                    "debito" => $totalAbonado,
                    "credito" => $totalAbonado,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);
                
                $documentoGeneral->addRow($doc, $cuentaRecord->naturaleza_egresos);
            }

            //GUARDAMOS EL VALOR RESTANTE COMO ANTICIPO
            if ($valorPagado) {

                $formaPagoAnticipos = FacFormasPago::with('cuenta')
                    ->whereHas('cuenta', function ($query) {
                    $query->whereHas('tipos_cuenta', function ($q) {
                        $q->whereIn('id_tipo_cuenta', [4,8]);
                    });
                })->first();

                $comprobante = Comprobantes::find($pago->id_comprobante);
                
                $doc = new DocumentosGeneral([
                    "id_cuenta" => $formaPagoAnticipos->cuenta->id,
                    "id_nit" => $formaPagoAnticipos->cuenta->exige_nit ? $pago->id_nit : null,
                    "id_centro_costos" => null,
                    "concepto" => $formaPagoAnticipos->cuenta->exige_concepto ? 'ANTICIPOS COMPROBANTE: '.$extracto->concepto : null,
                    "documento_referencia" => $formaPagoAnticipos->cuenta->exige_documento_referencia ? $consecutivo : null,
                    "debito" => $valorPagado,
                    "credito" => $valorPagado,
                    "created_by" => request()->user()->id,
                    "updated_by" => request()->user()->id
                ]);

                $documentoGeneral->addRow($doc, $formaPagoAnticipos->cuenta->naturaleza_egresos);
            }

            //AGREGAR MOVIMIENTO CONTABLE PAGO
            $pagoPagos = $pago->pagos[0];
            $formaPago = $this->findFormaPago($pagoPagos->id_forma_pago);
            
            $doc = new DocumentosGeneral([
                'id_cuenta' => $formaPago->cuenta->id,
                'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
                'id_centro_costos' => null,
                'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL PAGO: '.$nit->nombre_nit.' - '.$consecutivo : null,
                'documento_referencia' => null,
                'debito' => $pagoPagos->valor,
                'credito' => $pagoPagos->valor,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_egresos);

            $this->updateConsecutivo($pago->id_comprobante, $consecutivo);

            if (!$documentoGeneral->save()) {

				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], Response::HTTP_UNPROCESSABLE_ENTITY);
			}

            //GUARDAMOS RECIBO
            $pago->consecutivo = $consecutivo;
            $pago->estado = 1;
            $pago->observacion = $request->get('observacion');
            $pago->save();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $documentoGeneral->getRows(),
                'message'=> 'Pago aprobado con exito!'
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
            'id' => 'required|exists:sam.con_pagos,id',
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

            ConPagos::where('id', $request->get('id'))
                ->where('estado', 2)
                ->delete();

            ConPagoPagos::where('id_pago', $request->get('id'))
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
        $pago = ConPagos::whereId($id)
            ->with('comprobante')
            ->first();

        if(!$pago) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'El pago no existe'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        $data = (new PagosPdf($empresa, $pago))->buildPdf()->getData();
 
        return (new PagosPdf($empresa, $pago))
            ->buildPdf()
            ->showPdf();
    }

    public function showPdfPublic(Request $request)
    {
        $token_db = base64_decode($request->get('token_db'));
        $empresa = Empresa::where('token_db', $token_db)->first();

		Config::set('database.connections.sam.database', $token_db);
        
        $pago = ConPagos::whereId($request->get('id'))
            ->with('comprobante')
            ->first();

        if(!$pago) {
            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'El pago no existe'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = (new PagosPdf($empresa, $pago))->buildPdf()->getData();
 
        return (new PagosPdf($empresa, $pago))
            ->buildPdf()
            ->showPdf();
    }

    private function addFormaPago($documentoReferencia, $formaPago, $nit, $pagoItem, $pago, $valor, $saldo)
    {
        ConPagoPagos::create([
            'id_pago' => $pago->id,
            'id_forma_pago' => $pagoItem->id,
            'valor' => $valor,
            'saldo' => $saldo,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        $doc = new DocumentosGeneral([
            'id_cuenta' => $formaPago->cuenta->id,
            'id_nit' => $formaPago->cuenta->exige_nit ? $nit->id : null,
            'id_centro_costos' => null,
            'concepto' => $formaPago->cuenta->exige_concepto ? 'TOTAL PAGO: '.$nit->nombre_nit.' - '.$pago->consecutivo : null,
            'documento_referencia' => $documentoReferencia,
            'debito' => $valor,
            'credito' => $valor,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        return $doc;
    }

    private function formatExtracto($extracto)
    {
        $editando = property_exists($extracto, 'edit') ? true : false;

        $this->id_pago++;
        return [
            'id' => $this->id_pago,
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
            'cuenta_pago' => true,
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
        $this->id_pago++;
        $anticipoCuenta = (new Extracto(
            $idNit,
            null,
            null,
            Carbon::now()->format('Y-m-d H:i:s'),
            $cuenta->id
        ))->anticipos()->first();

        return [
            'id' => $this->id_pago,
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
            'cuenta_pago' => false,
        ];
    }

    private function createFacturaPago($request)
    {
        $this->calcularTotales($request->get('movimiento'));
        $this->calcularFormasPago($request->get('pagos')); 

        $pago = ConPagos::create([
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

        return $pago;
    }

    private function createPagoComprobante($request)
    {
        $valorTotal = $request->get('valor_comprobante') ? $request->get('valor_comprobante') : $request->get('valor_pago');

        $pago = ConPagos::create([
            'id_nit' => $request->get('id_nit'),
            'id_comprobante' => $request->get('id_comprobante'),
            'fecha_manual' => $request->get('fecha_pago'),
            'consecutivo' => $request->get('consecutivo'),
            'total_abono' => $valorTotal,
            'total_anticipo' => 0,
            'observacion' => '',
            'estado' => $request->get('valor_comprobante') ? 2 : 1,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        if($request->comprobante) {
            $empresaId = request()->user()->id_empresa;

            $image = $request->comprobante;
            $ext = explode(";", explode("/",explode(",", $image)[0])[1])[0];
            $image = str_replace('data:image/'.$ext.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'comprobante_'.uniqid().'.'. $ext;
            
            $urlImagen = "imagen/{$empresaId}/pagos_pagos/{$imageName}";

            Storage::disk('do_spaces')->put($urlImagen, base64_decode($image), 'public');

            $archivo = new ArchivosGenerales([
                'tipo_archivo' => 'imagen',
                'url_archivo' => $urlImagen,
                'estado' => 1,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $archivo->relation()->associate($pago);
            $pago->archivos()->save($archivo);
        }

        return $pago;
    }

    private function calcularTotales ($movimientos)
    {
        foreach ($movimientos as $movimiento) {
            $movimiento = (object)$movimiento;
            if ($movimiento->cuenta_pago) {
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
            if ($tipoCuenta->id_tipo_cuenta == 3 || $tipoCuenta->id_tipo_cuenta == 7) {
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
}
