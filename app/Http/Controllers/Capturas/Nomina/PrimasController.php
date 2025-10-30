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
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\Nomina\NomPrimas;
use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomVacaciones;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomVacacionDetalles;
use App\Models\Sistema\Nomina\NomNovedadesGenerales;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class PrimasController extends Controller
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
        return view('pages.capturas.primas.primas-view');
    }

    public function generate (Request $request)
    {
        $rules = [
            'fecha_desde' => 'required|date_format:Y-m-d',
            'fecha_hasta' => 'required|date_format:Y-m-d'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()){
            
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {

            $contratos = NomContratos::with([
                    'periodo_pago' => fn($q) => $this->filterPaymentPeriods($q, $request),
                    'periodo',
                    'periodo_pago.detalles',
                    'periodo_pago.detalles.concepto',
                    'empleado'
                ])
                ->whereIn('tipo_salario', [
                    NomContratos::TIPO_SALARIO_NORMAL,
                    NomContratos::TIPO_SALARIO_INTEGRAL
                ])
                ->where('estado', NomContratos::ESTADO_ACTIVO)
                ->where(fn($q) => $this->filterContractDates($q, $request))
                ->get();

            $consecutivo = 0;

            $primaEmpleados = $contratos->filter(fn($c) => $c->periodo_pago->isNotEmpty())
                ->map(function ($contrato) use ($request, &$consecutivo) {
                    $consecutivo++;
                    $dateRange = $this->getContractDateRange($contrato, $request);
                    $periodosPago = $this->getValidPaymentPeriods($contrato, $dateRange);

                    if ($periodosPago->isEmpty()) return null;

                    $base = $this->calculateBase($periodosPago);
                    $dias = $this->calculateDays($periodosPago, $contrato->periodo);

                    $calculations = $this->calculatePrima($base, $dias);

                    return [
                        'id' => $consecutivo,
                        'id_contrato' => $contrato->id,
                        'id_empleado' => $contrato->id_empleado,
                        'empleado' => $contrato->empleado->nombre_completo,
                        'numero_documento' => $contrato->empleado->numero_documento,
                        'fecha_inicio' => $dateRange['inicio'],
                        'fecha_fin' => $dateRange['fin'],
                        'base' => $base,
                        'dias' => $dias,
                        'promedio' => $calculations['promedio'],
                        'valor' => $calculations['valor'],
                        'dias_promedio' => $calculations['diasPromedio'],
                        'editado' => false,
                    ];
                })
                ->filter()
                ->values();

            return response()->json([
                "success" => true,
                'data' => $primaEmpleados,
                "message" => ''
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function detalles (Request $request)
    {
        $rules = [
            'id_contrato' => 'required|exists:sam.nom_contratos,id',
            'fecha_desde' => 'required|date_format:Y-m-d',
            'fecha_hasta' => 'required|date_format:Y-m-d'
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

            $contrato = NomContratos::find($request->get('id_contrato'));

            $fechaInicio = $contrato->fecha_inicio_contrato > $request->get('fecha_desde')
                ? $contrato->fecha_inicio_contrato
                : $request->get('fecha_desde');

            $fechaFin = $contrato->fecha_fin_contrato && $contrato->fecha_fin_contrato < $request->get('fecha_hasta')
                ? $contrato->fecha_fin_contrato
                : $request->get('fecha_hasta');

            $periodosPago = NomPeriodoPagos::select('id')
                ->where('fecha_inicio_periodo', '>=', $fechaInicio)
                ->where('fecha_fin_periodo', '<=', $fechaFin)
                ->where('id_contrato', $request->get('id_contrato'))
                ->pluck('id');

            $detallePeriodoPago = NomPeriodoPagoDetalles::select([
                    'nom_periodo_pago_detalles.*',
                    'nom_periodo_pagos.fecha_inicio_periodo',
                    'nom_periodo_pagos.fecha_fin_periodo',
                    'nom_conceptos.nombre AS concepto',
                    'nom_conceptos.tipo_concepto',
                    DB::raw(
                        "IF (tipo_unidad = 0, CAST(unidades / horas_dia AS DECIMAL(5,2)), unidades) AS dias"
                    )
                ])
                    ->join('nom_periodo_pagos', 'nom_periodo_pago_detalles.id_periodo_pago', 'nom_periodo_pagos.id')
                    ->join('nom_conceptos', 'nom_periodo_pago_detalles.id_concepto', 'nom_conceptos.id')
                    ->join('nom_contratos', 'nom_periodo_pagos.id_contrato', 'nom_contratos.id')
                    ->join('nom_periodos', 'nom_contratos.id_periodo', 'nom_periodos.id')
                    ->whereIn('id_periodo_pago', $periodosPago)
                    ->where('nom_conceptos.base_prima', 1)
                    ->orderBy('fecha_fin_periodo');

            return response()->json([
                'success'=>	true,
                'data' => $detallePeriodoPago->get(),
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

    public function create(Request $request)
    {
        $rules = [
            'fecha_desde' => 'required|date_format:Y-m-d',
            'fecha_hasta' => 'required|date_format:Y-m-d',
            'fecha_novedad' => 'required|date_format:Y-m-d',
            'fecha_personalizada' => 'nullable|boolean',
            'data' => 'required|array',
            'data.*.id_contrato' => 'required|exists:sam.nom_contratos,id,estado,'.NomContratos::ESTADO_ACTIVO,
            'data.*.id_empleado' => 'required|exists:sam.nits,id',
            'data.*.base' => 'required|numeric',
            'data.*.dias' => 'required|numeric|min:0',
            'data.*.promedio' => 'required|numeric|min:0',
            'data.*.valor' => 'required|numeric|min:0',
            'data.*.dias_promedio' => 'required|numeric|min:0',
            'data.*.editado' => 'required|boolean',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::connection('sam')->beginTransaction();

            $primaData = $request->get('data');
            $fechaDesde = $request->get('fecha_desde');
            $fechaHasta = $request->get('fecha_hasta');
            $fechaPersonalizada = $request->get('fecha_personalizada', false);

            $concepto = NomConceptos::where('codigo', NomConceptos::CODE_PRIMA)->first();
            
            if (!$concepto) {
                throw new Exception('Concepto de prima no encontrado');
            }

            $periodosPago = [];
            $primasCreadas = [];

            foreach ($primaData as $prima) {
                $idContrato = $prima['id_contrato'];

                // Gestionar periodo de pago
                if ($fechaPersonalizada) {
                    $periodoPago = NomPeriodoPagos::firstOrCreate(
                        [
                            'id_contrato' => $idContrato,
                            'id_empleado' => $prima['id_empleado'],
                            'fecha_inicio_periodo' => $prima['fecha_inicio'],
                            'fecha_fin_periodo' => $prima['fecha_fin'],
                            'estado' => NomPeriodoPagos::ESTADO_PENDIENTE,
                        ],
                        [
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ]
                    );
                    $periodosPago[$idContrato] = $periodoPago;
                } else {
                    if (!isset($periodosPago[$idContrato])) {
                        $periodoPago = NomPeriodoPagos::where('id_contrato', $idContrato)
                            ->whereDate('fecha_inicio_periodo', '>=', $prima['fecha_inicio'])
                            ->whereDate('fecha_fin_periodo', '<=', $prima['fecha_fin'])
                            ->first();

                        if (!$periodoPago) {
                            throw new Exception("No se encontró un periodo de pago válido para el contrato {$idContrato}");
                        }
                        $periodosPago[$idContrato] = $periodoPago;
                    }
                }

                $periodoPago = $periodosPago[$idContrato];

                // Crear prima
                $primaCreada = $periodoPago->primas()->create([
                    'id_empleado' => $prima['id_empleado'],
                    'id_periodo_pago' => $periodoPago->id,
                    'fecha_inicio' => $fechaDesde,
                    'fecha_fin' => $fechaHasta,
                    'base' => $prima['base'],
                    'dias' => $prima['dias'],
                    'promedio' => $prima['promedio'],
                    'valor' => $prima['valor'],
                    'dias_promedio' => $prima['dias_promedio'],
                    'editado' => $prima['editado'],
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                // Crear novedad
                $novedad = new NomNovedadesGenerales([
                    'id_empleado' => $prima['id_empleado'],
                    'id_contrato' => $idContrato,
                    'id_concepto' => $concepto->id,
                    'id_periodo_pago' => $periodoPago->id,
                    'tipo_unidad' => NomPeriodoPagoDetalles::TIPO_UNIDAD_DIAS,
                    'unidades' => $prima['dias'],
                    'valor' => $prima['valor'],
                    'porcentaje' => 0,
                    'base' => $prima['base'],
                    'observacion' => "Prima generada para el periodo {$fechaDesde} - {$fechaHasta}",
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                $periodoPago->novedades()->save($novedad);

                $primasCreadas[] = $primaCreada;
            }

            // Calcular nóminas para todos los periodos afectados
            $periodosIds = array_map(fn($pp) => $pp->id, $periodosPago);

            (new CalcularPeriodo())->calcularNominas(
                $request->get('fecha_novedad'),
                null,
                $periodosIds);

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'data' => $primasCreadas,
                'message' => 'Primas creadas con éxito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            DB::connection('sam')->rollBack();
            
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    protected function filterPaymentPeriods($query, Request $request)
    {
        return $query->whereDate('fecha_inicio_periodo', '>=', $request->fecha_desde)
            ->whereDate('fecha_fin_periodo', '<=', $request->fecha_hasta);
    }

    protected function filterContractDates($query, Request $request)
    {
        return $query->where(function($q) use ($request) {
            $q->where('fecha_fin_contrato', '>=', $request->fecha_hasta)
            ->orWhereNull('fecha_fin_contrato');
        });
    }

    protected function getContractDateRange($contrato, Request $request)
    {
        return [
            'inicio' => $contrato->fecha_inicio_contrato > $request->fecha_desde 
                ? $contrato->fecha_inicio_contrato 
                : $request->fecha_desde,
            'fin' => $contrato->fecha_fin_contrato && $contrato->fecha_fin_contrato < $request->fecha_hasta
                ? $contrato->fecha_fin_contrato
                : $request->fecha_hasta
        ];
    }

    protected function getValidPaymentPeriods($contrato, $dateRange)
    {
        return $contrato->periodo_pago
            ->where('fecha_inicio_periodo', '>=', $dateRange['inicio'])
            ->where('fecha_fin_periodo', '<=', $dateRange['fin']);
    }

    protected function calculateBase($periodosPago)
    {
        return $periodosPago->sum(fn($pp) => $pp->detalles
            ->filter(fn($det) => $det->concepto->base_prima)
            ->sum('valor'));
    }

    protected function calculateDays($periodosPago, $periodo)
    {
        $excludedTypes = ['auxilio_transporte', 'heds', 'hens', 'heddfs', 'hendfs'];

        return $periodosPago->sum(fn($pp) => $pp->detalles
            ->filter(fn($det) => $det->concepto->base_prima)
            ->sum(function ($det) use ($periodo, $excludedTypes) {
                if (in_array($det->concepto->tipo_concepto, $excludedTypes)) {
                    return 0;
                }

                $dias = $det->unidades;
                
                if ($det->tipo_unidad == NomPeriodoPagoDetalles::TIPO_UNIDAD_HORAS) {
                    $dias = $dias / $periodo->horas_dia;
                }

                return $det->valor < 0 ? $dias * -1 : $dias;
            }));
    }

    protected function calculatePrima($base, $dias)
    {
        $DIAS_YEAR = 360;
        $DIAS_MES = 30;
        $DIAS_SEMESTRE = 180;

        $promedio = $dias > 0 ? ($base / $dias) * $DIAS_MES : 0;
        $valor = round($dias * $promedio) / $DIAS_YEAR;
        $diasPromedio = round(($dias * 15) / $DIAS_SEMESTRE);

        return compact('promedio', 'valor', 'diasPromedio');
    }

}