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
use Illuminate\Pagination\LengthAwarePaginator;
//MODELS
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class CausarNominaController extends Controller
{
    protected $messages = null;

    public function __construct()
    {
        $this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El camNipo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
    }

    public function index ()
    {
        return view('pages.capturas.causar_nomina.causar_nomina-view');
    }

    public function comboPeriodoPago(Request $request)
    {
        $periodoPago = NomPeriodoPagos::select(
            '*',
            DB::raw("CONCAT(fecha_inicio_periodo, ' al ', fecha_fin_periodo) as text")
        );

        if ($request->has('mes')) {
            $periodoPago->where(function($query) use ($request) {
                $query->where(DB::raw("DATE_FORMAT(fecha_inicio_periodo, '%Y-%m')"), '<=', $request->get('mes'))
                    ->where(DB::raw("DATE_FORMAT(fecha_fin_periodo, '%Y-%m')"), '>=', $request->get('mes'));
            });
        }

        return $periodoPago
            ->groupByRaw('fecha_inicio_periodo, fecha_fin_periodo')
            ->orderBy('id', 'DESC')
            ->paginate(20);
    }

    public function comboMeses(Request $request)
    {
        $endDate = CarbonImmutable::now()->startOfMonth()->addMonthsNoOverflow(3);
        $startDate = $endDate->subYearsNoOverflow(1);

        $months = collect();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $months->push([
                'id' => $currentDate->format('Y-m'),
                'value' => $currentDate->format('Y-m'),
                'text' => $currentDate->isoFormat('YYYY - MMMM'),
            ]);
            $currentDate = $currentDate->addMonthNoOverflow();
        }

        // Ordenar descendente
        $sortedMonths = $months->sortBy(function ($item) {
            return CarbonImmutable::createFromFormat('Y-m', $item['value']);
        })->values();
        
        // Paginación manual
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $paginated = new LengthAwarePaginator(
            $sortedMonths->forPage($page, $perPage),
            $sortedMonths->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json($paginated);
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

    public function calcularNomina(Request $request)
    {
        $rules = [
            'mes' => 'required',
            'id' => 'nullable|exists:sam.nom_periodo_pagos,id',
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

            $mes = CarbonImmutable::parse($request->get('meses'));
            $idsPeriodoPago = $request->get('id');
            $fechasPeriodos = (new CalcularPeriodo())->calcularNominas($mes->format('Y-m'), null, $idsPeriodoPago);

            return response()->json([
                "success"=> true,
                'data' => [],
                "message"=> 'Calculo realizado con exito'
            ], Response::HTTP_OK);
            
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function detallePeriodo(Request $request)
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

}