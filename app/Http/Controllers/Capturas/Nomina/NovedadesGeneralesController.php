<?php

namespace App\Http\Controllers\Capturas\Nomina;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Helpers\Nomina\CalcularPeriodo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\Nomina\NomPeriodos;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomNovedadesGenerales;

class NovedadesGeneralesController extends Controller
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
        return view('pages.capturas.novedades_generales.novedades_generales-view');
    }

    public function generate (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = 20;

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $searchValue = $request->get('search');
        $searchValue = isset($searchValue) ? $searchValue["value"] : null;

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        $nomNovedadesGenerales = NomNovedadesGenerales::with(
                'empleado',
                'periodo_pago',
                'concepto',
            )
            ->select(
                '*',
                DB::raw("DATE_FORMAT(nom_novedades_generales.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(nom_novedades_generales.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'nom_novedades_generales.created_by',
                'nom_novedades_generales.updated_by'
            )
        ->orderBy('id', 'desc');

        if ($request->get('id_empleado')) {
            $nomNovedadesGenerales->where('id_empleado', $request->get('id_empleado'));
        }

        if ($request->get('id_periodo_pago')) {
            $nomNovedadesGenerales->where('id_periodo_pago', $request->get('id_periodo_pago'));
        }

        if ($request->get('id_concepto')) {
            $nomNovedadesGenerales->where('id_concepto', $request->get('id_concepto'));
        }

        $totalNomNovedadesGenerales = $nomNovedadesGenerales->count();
        $nomNovedadesGenerales = $nomNovedadesGenerales->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $totalNomNovedadesGenerales,
            'iTotalDisplayRecords' => $totalNomNovedadesGenerales,
            'data' => $nomNovedadesGenerales->get(),
            'perPage' => $rowperpage,
            'message'=> 'Novedades generales cargados con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'id_empleado' => 'required|exists:sam.nits,id',
            'id_concepto' => 'required|exists:sam.nom_conceptos,id',
            "fecha_desde" => "required|date_format:Y-m-d",
            "fecha_hasta" => "required|date_format:Y-m-d",
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
            
            $contrato = NomContratos::where('id_empleado', $request->get('id_empleado'))
                ->with('periodo')
                ->where('estado', 1)
                ->first();

            if (!$contrato) {
                $validator->errors()->add('id_empleado', 'El empleado no tiene un contrato activo.');

                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>$validator->errors()
                ], 422);
            }
            
            $concepto = NomConceptos::where('id', $request->get('id_concepto'))->first();

            $dataNovedades = [
                'id_empleado' => $request->get("id_empleado"),
                'id_periodo_pago' => null,
                'id_concepto' => $request->get("id_concepto"),
                'tipo_unidad' => $concepto->unidad,
                'unidades' => $request->get("unidades"),
                'valor' => $request->get("valor"),
                'porcentaje' => $request->get("porcentaje"),
                'base' => 0,
                'observacion' => '',
                'fecha_inicio' => $request->get("fecha_desde"),
                'fecha_fin' => $request->get("fecha_hasta"),
                'hora_inicio' => $request->get("hora_desde"),
                'hora_fin' => $request->get("hora_hasta"),
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ];

            $periodoPago = null;
            $fechasPeriodos = $this->periodoPago($request->get("fecha_desde"), $contrato);

            foreach ($fechasPeriodos as $key => $fechaPeriodo) {

                $fechaInicio = Carbon::createFromFormat('Y-m-d', $request->get("fecha_desde"));
                $fechaFin = Carbon::createFromFormat('Y-m-d', $fechaPeriodo['fecha_fin']);

                if($fechaFin->greaterThanOrEqualTo($fechaInicio)){
                    $periodoPago = NomPeriodoPagos::firstOrCreate(
                        [
                            "id_empleado" => $contrato->id_empleado,
                            "id_contrato" => $contrato->id,
                            "fecha_inicio_periodo" => $fechaPeriodo['fecha_inicio'],
                            "fecha_fin_periodo" => $fechaPeriodo['fecha_fin'],
                        ],
                        [
                            "estado" => NomPeriodoPagos::ESTADO_PENDIENTE,
                        ]
                    );
                    break;
                }
            }

            $dataNovedades['id_periodo_pago'] = $periodoPago->id;

            $novedadesGenerales = NomNovedadesGenerales::create($dataNovedades);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $novedadesGenerales,
                'message'=> 'Novedad general creada con exito!'
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

    public function update (Request $request)
    {
        $rules = [
            'id' => 'required|exists:sam.nom_novedades_generales,id',
            'id_empleado' => 'required|exists:sam.nits,id',
            'id_concepto' => 'required|exists:sam.nom_conceptos,id',
            "fecha_desde" => "required|date_format:Y-m-d",
            "fecha_hasta" => "required|date_format:Y-m-d",
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

            $nodedadGeneral = NomNovedadesGenerales::with('periodo_pago')
                ->where('id', $request->get('id'))
                ->first();

            if ($nodedadGeneral->periodo_pago->estado == 2) {
                $validator->errors()->add('periodo_pago', 'El periodo pago ya se encuentra pagado.');

                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>$validator->errors()
                ], 422);
            }
            
            $contrato = NomContratos::where('id_empleado', $request->get('id_empleado'))
                ->with('periodo')
                ->where('estado', 1)
                ->first();

            if (!$contrato) {
                $validator->errors()->add('id_empleado', 'El empleado no tiene un contrato activo.');

                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>$validator->errors()
                ], 422);
            }
            
            $concepto = NomConceptos::where('id', $request->get('id_concepto'))->first();

            $periodoPago = null;
            $fechasPeriodos = $this->periodoPago($request->get("fecha_desde"), $contrato);

            foreach ($fechasPeriodos as $key => $fechaPeriodo) {

                $fechaInicio = Carbon::createFromFormat('Y-m-d', $request->get("fecha_desde"));
                $fechaFin = Carbon::createFromFormat('Y-m-d', $fechaPeriodo['fecha_fin']);

                if($fechaFin->greaterThanOrEqualTo($fechaInicio)){
                    $periodoPago = NomPeriodoPagos::firstOrCreate(
                        [
                            "id_empleado" => $contrato->id_empleado,
                            "id_contrato" => $contrato->id,
                            "fecha_inicio_periodo" => $fechaPeriodo['fecha_inicio'],
                            "fecha_fin_periodo" => $fechaPeriodo['fecha_fin'],
                        ],
                        [
                            "estado" => NomPeriodoPagos::ESTADO_PENDIENTE,
                        ]
                    );
                    break;
                }
            }

            $novedadesGenerales = NomNovedadesGenerales::where('id', $request->get("id"))
                ->update([
                    'id_empleado' => $request->get("id_empleado"),
                    'id_periodo_pago' => $periodoPago->id,
                    'id_concepto' => $request->get("id_concepto"),
                    'tipo_unidad' => $concepto->unidad,
                    'unidades' => $request->get("unidades"),
                    'valor' => $request->get("valor"),
                    'porcentaje' => $request->get("porcentaje"),
                    'base' => 0,
                    'observacion' => '',
                    'fecha_inicio' => $request->get("fecha_desde"),
                    'fecha_fin' => $request->get("fecha_hasta"),
                    'hora_inicio' => $request->get("hora_desde"),
                    'hora_fin' => $request->get("hora_hasta"),
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $novedadesGenerales,
                'message'=> 'Novedade generale actualizada con exito!'
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

    public function delete (Request $request)
    {
        try {

            $nodedadGeneral = NomNovedadesGenerales::with('periodo_pago')
                ->where('id', $request->get('id'))
                ->first();
            
            if ($nodedadGeneral->periodo_pago->estado == 2) {
                $validator->errors()->add('periodo_pago', 'El periodo pago ya se encuentra pagado.');

                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>$validator->errors()
                ], 422);
            }

            $nodedadGeneral->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Novedad general eliminada con exito!'
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

    protected function periodoPago ($fecha, $contrato)
    {
        $periodo = $contrato->periodo;

        if ($periodo->tipo_dia_pago == NomPeriodos::TIPO_DIA_PAGO_ORDINAL) {
            $periodoDiasOrdinales = str_replace(' ', '', $periodo->periodo_dias_ordinales);
            $periodoDiasOrdinales = explode(',', $periodo->periodo_dias_ordinales);
            $fechasPeriodos = (new CalcularPeriodo())->getPeriodosOrdinales($fecha, $periodoDiasOrdinales);
        }

        if ($periodo->tipo_dia_pago == NomPeriodos::TIPO_DIA_PAGO_CALENDARIO) {
            $fechasPeriodos = (new CalcularPeriodo())->getPeriodosCalendario($fecha, $contrato);
        }

        return $fechasPeriodos;
    }

}