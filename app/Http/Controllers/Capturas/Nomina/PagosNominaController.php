<?php

namespace App\Http\Controllers\Capturas\Nomina;

use DB;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Helpers\Nomina\CalcularPeriodo;
use Illuminate\Support\Facades\Validator;
//HELPER
use App\Helpers\Documento;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomNovedadesGenerales;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class PagosNominaController extends Controller
{
    protected $messages = null;

    public function __construct()
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
        return view('pages.capturas.pagos.pagos-view');
    }

    public function generate (Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");
    
            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $mes = CarbonImmutable::parse($request->get('meses'));
            
            $periodoPago = NomPeriodoPagos::with(
                    'empleado',
                    'sumDetalles'
                )
                ->select(
                    '*',
                    DB::raw("CONCAT(fecha_inicio_periodo, '-', fecha_fin_periodo) as fecha_periodo")
                )
                ->when($request->get('estado'), function ($q) use ($request) {
                    $q->where('estado', $request->get('estado'));
                })
                ->when($request->get('meses'), function ($q) use ($mes) {
                    $q->where('fecha_fin_periodo', 'LIKE', $mes->format('Y-m') . '%');
                })
                ->when($request->get('fecha_inicio_periodo'), function ($q) use ($request) {
                    $q->where('fecha_inicio_periodo', $request->get('fecha_inicio_periodo'));
                })
                ->when($request->get('fecha_fin_periodo'), function ($q) use ($request) {
                        $q->where('fecha_fin_periodo', $request->get('fecha_fin_periodo'));
                })
                ->orderBy('fecha_periodo');

            $totalPeriodoPago = $periodoPago->count();
            
            $periodoPagoPaginate = $periodoPago->skip($start)
                ->take($rowperpage);
    
            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $totalPeriodoPago,
                'iTotalDisplayRecords' => $totalPeriodoPago,
                'data' => $periodoPagoPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Periodo pago generados con exito!'
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

    public function detalle (Request $request)
    {
        try {
            $detallePeriodoPago = NomPeriodoPagoDetalles::select([
                    '*',
                    DB::raw('IF (valor >= 0, valor, 0) AS devengados'),
                    DB::raw('IF (valor < 0, valor, 0) AS deducciones'),
                ])
                ->with([
                    'concepto:id,codigo,nombre',
                    'periodoPago:id,id_empleado,estado',
                    'periodoPago.empleado:id,razon_social,primer_nombre,otros_nombres,primer_apellido,segundo_apellido,numero_documento,digito_verificacion',
                ])
                ->when($request->has('id_periodo_pago'), function ($q) use ($request) {
                    $q->where('id_periodo_pago', $request->get('id_periodo_pago'));
                })
                ->orderBy('id_concepto')
                ->get();

            $sumDevengados = $detallePeriodoPago->sum('devengados');
            $sumDeducciones = abs($detallePeriodoPago->sum('deducciones'));
            $neto = $sumDevengados - $sumDeducciones;

            return response()->json([
                'success'=>	true,
                'data' => $detallePeriodoPago,
                'totales' => (object)[
                    'devengados' => $sumDevengados,
                    'deducciones' => $sumDeducciones,
                    'neto' => $neto,
                ],
                'message'=> 'Periodo pago generados con exito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function pagar (Request $request)
    {
        $rules = [
            'ids_periodos_pago' => 'required|array'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $configComprobanteNomina = VariablesEntorno::where('nombre', "id_comprobante_nomina")->pluck('valor')->first();
        $configCuentasXpagarEmpleados = VariablesEntorno::where('nombre', "cuenta_x_pagar_empleados")->pluck('valor')->first();
        $configCuentasContablesEmpleados = VariablesEntorno::where('nombre', "cuenta_contable_pago_nomina")->pluck('valor')->first();

        $comprobante = Comprobantes::where('id', $configComprobanteNomina)->first();
        $cuentaXpagarEmpleados = PlanCuentas::where('cuenta', $configCuentasXpagarEmpleados)->first();
        $cuentaContablesEmpleados = PlanCuentas::where('cuenta', $configCuentasContablesEmpleados)->first();

        if (!$comprobante) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> 'El comprobante de nómina no existe o no está configurado correctamente'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$cuentaXPagarEmpleado) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> 'La cuenta por pagar a empleados no existe o no está configurado correctamente'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$cuentaContablesEmpleados) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> 'La cuenta contable de empleados no existe o no está configurado correctamente'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {

            DB::connection('sam')->beginTransaction();

            $periodosPago = NomPeriodoPagos::with([
                    'detalles.concepto',
                    'contrato.fondo_salud',
                    'contrato.fondo_pension',
                    'empleado'
                ])
                ->whereIn('id', $request->get('ids_periodos_pago'))
                ->get();

            $errors = [];
            $processedCount = 0;

            $facDocumento = FacDocumentos::create([
                'id_nit' => $docGroup[0]->id_tercero_erp,
                'id_comprobante' => $docGroup[0]->id_comprobante,
                'fecha_manual' => $docGroup[0]->fecha_factura,
                'consecutivo' => $docGroup[0]->consecutivo_factura,
                'token_factura' => $tokenFactura,
                'debito' => 0,
                'credito' => 0,
                'saldo_final' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            foreach ($periodosPago as $periodoPago) {
                // Validar estado del período
                if ($periodoPago->estado === NomPeriodoPagos::ESTADO_PENDIENTE) {
                    $errorTitle = $periodoPago->empleado->nombre_completo;
                    $errors[$errorTitle] = ['periodo_pago' => "El periodo seleccionado $periodoPago->fecha_inicio_periodo - $periodoPago->fecha_fin_periodo no se encuentra causado."];
                    continue;
                }

                if ($periodoPago->estado === NomPeriodoPagos::ESTADO_PAGADO) {
                    $errorTitle = $periodoPago->empleado->nombre_completo;
                    $errors[$errorTitle] = ['periodo_pago' => "El periodo seleccionado $periodoPago->fecha_inicio_periodo - $periodoPago->fecha_fin_periodo ya se encuentra pagado."];
                    continue;
                }

                // Procesar el período
                $processResult = $this->processSinglePeriodo(
                    $periodoPago,
                    $cuentaXPagarEmpleado,
                    $cuentaContablesEmpleados,
                    $comprobante
                );
                
                if (!$processResult['success']) {
                    $errors = array_merge($errors, $processResult['errors']);
                    continue;
                }

                $processedCount++;
            }

            // Si hay errores en algún período, hacer rollback completo
            if (!empty($errors)) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> $errors
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::connection('sam')->commit();

            return response()->json([
                "success"=> true,
                'data' => [],
                "message" => 'Pagos realizados con exito'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function processSinglePeriodo($periodoPago, $cuentaXPagarEmpleado, $cuentaContablesEmpleados, $comprobante)
    {
        $errors = [];
        $contrato = $periodoPago->contrato;
        $fecha = $periodoPago->fecha_fin_periodo;
        $consecutivo = $this->getNextConsecutive($comprobante, $fecha);
        $empleado = $periodoPago->empleado;

        $datosConcepto =  'Pago periodo ' . $periodoPago->fecha_fin_periodo . ' / ' . $empleado->numero_documento_completo . ' ' . $empleado->nombre_completo;

        $documentoGeneral = new Documento(
            $comprobante->id,
            $periodoPago,
            $fecha,
            $consecutivo,
            false
        );

        $documentoGeneral->setConceptoDefault('PAGO DE NÓMINA GENERADA POR EL SISTEMA');

        // Calcular valor neto
        $valorNeto = $periodoPago->detalles->sum('valor');

        $docEmpleado = new DocumentosGeneral([
            "id_cuenta" => $cuentaXPagarEmpleado->id,
            "id_nit" => $cuentaXPagarEmpleado->exige_nit ? $periodoPago->id_empleado : null,
            "id_centro_costos" => $cuentaXPagarEmpleado->exige_centro_costos ? $periodoPago->contrato->id_centro_costo : null,
            "documento_referencia" => $cuentaXPagarEmpleado->exige_documento_referencia ? $periodoPago->contrato->id : '',
            "concepto" => $cuentaXPagarEmpleado->exige_concepto ? 'PAGO NOMINA' : null,
            "credito" => 0,
            "debito" => $valorNeto,
        ]);

        $docEmpresa = new DocumentosGeneral([
            "id_cuenta" => $cuentaContablesEmpleados->id,
            "id_nit" => $cuentaContablesEmpleados->exige_nit ? $periodoPago->id_empleado : null,
            "id_centro_costos" => $cuentaContablesEmpleados->exige_centro_costos ? $periodoPago->contrato->id_centro_costo : null,
            "documento_referencia" => $cuentaContablesEmpleados->exige_documento_referencia ? $periodoPago->contrato->id : '',
            "concepto" => $cuentaContablesEmpleados->exige_concepto ? 'PAGO NOMINA' : null,
            "debito" => $valorNeto,
            "credito" => 0,
        ]);

        $documento->addRow($docEmpleado, PlanCuentas::DEBITO);
        $documento->addRow($docEmpresa, PlanCuentas::CREDITO);

        // Actualizar estado y guardar
        $periodoPago->estado = NomPeriodoPagos::ESTADO_PAGADO;
        $periodoPago->save();

        if (!$documentoGeneral->save()) {
            return [
                'success' => false,
                'errors' => $documentoGeneral->getErrors()
            ];
        }

        $documentoGeneral->setShouldUpdateConsecutivo(true);

        return ['success' => true];
    }

}