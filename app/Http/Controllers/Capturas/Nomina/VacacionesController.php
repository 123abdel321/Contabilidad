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
use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomVacaciones;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomVacacionDetalles;
use App\Models\Sistema\Nomina\NomNovedadesGenerales;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class VacacionesController extends Controller
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
        return view('pages.capturas.vacaciones.vacaciones-view');
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

        $nomVacaciones = NomVacaciones::with(
                'contrato',
                'empleado',
            )
            ->select(
                '*',
                DB::raw("DATE_FORMAT(nom_vacaciones.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(nom_vacaciones.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'nom_vacaciones.created_by',
                'nom_vacaciones.updated_by'
            )
        ->orderBy('id', 'desc');

        if($searchValue) {
            $nomVacaciones->where('nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('codigo', 'like', '%' .$searchValue . '%');
        }

        $totalNomVacaciones = $nomVacaciones->count();
        $nomVacaciones = $nomVacaciones->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $totalNomVacaciones,
            'iTotalDisplayRecords' => $totalNomVacaciones,
            'data' => $nomVacaciones->get(),
            'perPage' => $rowperpage,
            'message'=> 'Vacaciones cargadas con exito!'
        ]);
    }

    public function create (Request $request)
    {
		$rules = [
			"id_empleado" => "required|exists:sam.nits,id",
            "json_detalle" => "required",
            "promedio_otros" => "required",
            "salario_dia" => "required",
            "total_compensado" => "required",
            "total_disfrutado" => "required",
            "valor_dia_vacaciones" => "required",
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
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $jsonData = json_decode($request->get('json_detalle'));

            if(!isset($jsonData)){
                $validator->errors()->add('id_empleado', 'El empleado no se le realizo el calculo correctamente.');
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>$validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $vacaciones = NomVacaciones::create([
                "id_empleado" => $request->get("id_empleado"),
                "id_contrato" => $contrato->id,
                "metodo" => $request->get("metodo"),
                "fecha_inicio" => $request->get("fecha_inicio"),
                "fecha_fin" => $request->get("fecha_fin"),
                "dias_habiles" => $request->get("dias_habiles"),
                "dias_compensados" => $request->get("dias_compensados"),
                "dias_no_habiles" => $request->get("dias_no_habiles"),
                "promedio_otros" => $request->get("promedio_otros"),
                "salario_dia" => $request->get("salario_dia"),
                "valor_dia_vacaciones" => $request->get("valor_dia_vacaciones"),
                "total_disfrutado" => $request->get("total_disfrutado"),
                "total_compensado" => $request->get("total_compensado"),
                "observacion" => $request->get("observacion"),
                "salario_base" => $contrato->salario,
                "created_by" => request()->user()->id,
				"updated_by" => request()->user()->id
            ]);

            foreach ($jsonData as $data) {

                $value = [
                    'id_vacaciones' => $vacaciones->id,
                    'concepto' => $data->concepto,
                    'fecha' => Carbon::now()->format('Y-m-d'),
                    'valor' => $data->valor
                ];

                $detalle = new NomVacacionDetalles($value);
                $detalle->save();
            }

            $periodoPagos = NomPeriodoPagos::where('id_contrato', $contrato->id)
                ->where('id_empleado', $contrato->id_empleado)
                ->whereDate('fecha_fin_periodo', '>=', $request->get('fecha_inicio'))
                ->first();

            $this->novedadVacaciones($vacaciones, $contrato, $periodoPagos);
            
            (new CalcularPeriodo())->calcularNominas(
                CarbonImmutable::parse($vacaciones->fecha_fin)->format('Y-m'),
                [$vacaciones->id_empleado],
                [$periodoPagos->id]
            );

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $vacaciones,
                'message'=> 'Vacaciones creadas con exito!'
            ], Response::HTTP_OK);


        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
	}

    public function calcular (Request $request)
    {
        $rules = [
            'id_empleado' => 'required|exists:sam.nits,id',
            'metodo' => 'required'
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

            $contrato = $this->obtenerContratoActivo($request->id_empleado);
            $datosCalculo = $this->procesarPeriodosPago($contrato, $request->metodo);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'salario_dia' => $datosCalculo['salarioDia'],
                    'promedio_otros' => $datosCalculo['promedioOtros'],
                    'json_detalle' => $datosCalculo['jsonDetalle'],
                ],
                'message' => 'Vacaciones calculadas con éxito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } 
    }

    public function delete (Request $request)
    {
        $rules = [
            'id' => 'required|exists:sam.nom_vacaciones,id'
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

            $vacacion = NomVacaciones::find($request->get('id'));
            $vacacion->novedades()->delete();
            $vacacion->delete();

            (new CalcularPeriodo())->calcularNominas(
                CarbonImmutable::parse($vacacion->fecha_fin)->format('Y-m'),
                [$vacacion->id_empleado]
            );

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Vacaciones eliminadas con exito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ], 422);
        }
    }

    private function novedadVacaciones(NomVacaciones $vacaciones, NomContratos $contrato, NomPeriodoPagos $periodoPago)
	{
        $observacion = $vacaciones->observacion ?? 'VACACIONES';
		if($vacaciones->dias_compensados){

			$conceptoVacacionesCompensadas = NomConceptos::where('codigo', '034')->first();

			$vacacionesCompensadas = NomNovedadesGenerales::create([
				"id_empleado" => $contrato->id_empleado,
				"id_periodo_pago" => $periodoPago->id,
				"id_concepto" => $conceptoVacacionesCompensadas->id,
				"tipo_unidad" => 2,
				"unidades" => '',
				"valor" => $vacaciones->total_compensado,
				"porcentaje" => NULL,
				"base" => 0,
				"observacion" => "vacaciones/:{$vacaciones->id}: {$observacion}",
				"fecha_inicio" => $vacaciones->fecha_inicio,
				"fecha_fin" => $vacaciones->fecha_fin,
				"hora_inicio" => '',
				"hora_fin" => '',
				"created_by" => request()->user()->id,
				"updated_by" => request()->user()->id
			]);

			$vacacionesCompensadas->relation()->associate($vacaciones);
			$vacacionesCompensadas->save();
		}

		if($vacaciones->dias_habiles){
            
			$diasAgregados = 0;
        	$diasTotalesIncapacidad = intval($vacaciones->dias_habiles);
            
			while($diasTotalesIncapacidad > 0) {

				$fechaInicioPeriodo = Carbon::parse($vacaciones->fecha_inicio, 'UTC')->addDays($diasAgregados)->format('Y-m-d');
                $fechasPeriodos = (new CalcularPeriodo())->getPeriodoPago($contrato, $fechaInicioPeriodo);

				foreach ($fechasPeriodos as $fechasPeriodo) {

					$fechaInicio = Carbon::parse($vacaciones->fecha_inicio, 'UTC')->addDays($diasAgregados)->format('Y-m-d');
					$fecha1 = Carbon::createFromFormat('Y-m-d', $fechaInicio);
                	$fecha2 = Carbon::createFromFormat('Y-m-d', $fechasPeriodo['fecha_fin']);

					if($fecha2->greaterThanOrEqualTo($fecha1)){

						$diasDisponibles = $fecha1->diffInDays($fecha2)+1;
						$diasParaUsar = $diasTotalesIncapacidad; //DIAS DISPONIBLES EN EL PERIODO

						if($diasTotalesIncapacidad - $diasDisponibles > 0){
							$diasParaUsar = $diasDisponibles;
						}

						$periodoPago = NomPeriodoPagos::firstOrCreate(
							[
								"id_empleado" => $contrato->id_empleado,
								"id_contrato" => $contrato->id,
								"fecha_inicio_periodo" => $fechasPeriodo['fecha_inicio'],
								"fecha_fin_periodo" => $fechasPeriodo['fecha_fin'],
							],
							[
								"estado" => NomPeriodoPagos::ESTADO_PENDIENTE,
							]
						);

						if($diasParaUsar){

							$conceptoVacacionesComunes = NomConceptos::where('codigo', '033')->first();

							$vacacionesComunes = NomNovedadesGenerales::create([
								"id_empleado" => $contrato->id_empleado,
								"id_periodo_pago" => $periodoPago->id,
								"id_concepto" => $conceptoVacacionesComunes->id,
								"tipo_unidad" => 1,
								"unidades" => intval($diasParaUsar),
								"valor" => 0,
								"porcentaje" => NULL,
								"base" => 0,
								"observacion" => "vacaciones/:{$vacaciones->id}: {$observacion}",
								"fecha_inicio" => $vacaciones->fecha_inicio,
								"fecha_fin" => $vacaciones->fecha_fin,
								"hora_inicio" => '',
								"hora_fin" => '',
								"created_by" => request()->user()->id,
								"updated_by" => request()->user()->id
							]);
							$vacacionesComunes->relation()->associate($vacaciones);
							$vacacionesComunes->save();

							$conceptoHorasDiurnas = NomConceptos::where('codigo', '001')->first();

							$horasDiurnas = NomNovedadesGenerales::create([
								"id_empleado" => $contrato->id_empleado,
								"id_periodo_pago" => $periodoPago->id,
								"id_concepto" => $conceptoHorasDiurnas->id,
								"tipo_unidad" => 0,
								"unidades" => intval($diasParaUsar) * (intval($contrato->periodo->horas_dia) * -1),
								"valor" => 0,
								"porcentaje" => NULL,
								"base" => 0,
								"observacion" => "vacaciones/:{$vacaciones->id}: {$observacion}",
								"fecha_inicio" => NULL,
								"fecha_fin" => NULL,
								"hora_inicio" => '',
								"hora_fin" => '',
								"created_by" => request()->user()->id,
								"updated_by" => request()->user()->id
							]);
							$horasDiurnas->relation()->associate($vacaciones);
							$horasDiurnas->save();

							$conceptoAuxilioTransporte = NomConceptos::where('codigo', '015')->first();

							$auxilioTransporte = NomNovedadesGenerales::create([
								"id_empleado" => $contrato->id_empleado,
								"id_periodo_pago" => $periodoPago->id,
								"id_concepto" => $conceptoAuxilioTransporte->id,
								"tipo_unidad" => 1,
								"unidades" => intval($diasParaUsar) * -1,
								"valor" => 0,
								"porcentaje" => NULL,
								"base" => 0,
								"observacion" => "vacaciones/:{$vacaciones->id}: {$observacion}",
								"fecha_inicio" => NULL,
								"fecha_fin" => NULL,
								"hora_inicio" => '',
								"hora_fin" => '',
								"created_by" => request()->user()->id,
								"updated_by" => request()->user()->id
							]);
							$auxilioTransporte->relation()->associate($vacaciones);
							$auxilioTransporte->save();

							$diasTotalesIncapacidad-= $diasParaUsar;
							$diasAgregados+= $diasParaUsar;
						}
					}
				}
			}
		}
		return;
	}

    private function obtenerContratoActivo($idEmpleado)
    {
        $contrato = NomContratos::where('id_empleado', $idEmpleado)
            ->with('periodo_pago.detalles.concepto')
            ->where('estado', 1)
            ->first();

        if (!$contrato) {
            $validator = Validator::make([], []);
            $validator->errors()->add('id_empleado', 'El empleado no tiene un contrato activo.');
            throw new ValidationException($validator);
        }

        return $contrato;
    }

    private function procesarPeriodosPago($contrato, $metodo)
    {
        $acumuladores = [
            'sumaSalarios' => 0,
            'totalSalarios' => 0,
            'sumaOtros' => 0,
            'totalOtros' => 0,
            'sumaHENS' => 0,
            'totalHENS' => 0,
            'novedades' => []
        ];

        foreach ($contrato->periodo_pago as $pago) {
            $this->procesarDetallesPago($pago->detalles, $contrato->id_concepto_basico, $acumuladores);
        }

        return $this->calcularResultados($acumuladores, $contrato->salario, $metodo);
    }

    private function procesarDetallesPago($detalles, $idConceptoBasico, &$acumuladores)
    {
        foreach ($detalles as $detalle) {
            $concepto = $detalle->concepto;
            
            if (!$concepto->base_vacacion) {
                continue;
            }

            $this->clasificarConcepto($detalle, $concepto, $idConceptoBasico, $acumuladores);
        }
    }

    private function clasificarConcepto($detalle, $concepto, $idConceptoBasico, &$acumuladores)
    {
        // Concepto básico
        if ($concepto->id == $idConceptoBasico) {
            $acumuladores['sumaSalarios'] += $detalle->valor;
            $acumuladores['totalSalarios']++;
        } 
        // Horas extras nocturnas (código 007)
        elseif ($concepto->codigo == '007') {
            $acumuladores['sumaHENS'] += $detalle->valor;
            $acumuladores['totalHENS']++;
        } 
        // Otros conceptos
        else {
            $acumuladores['sumaOtros'] += $detalle->valor;
            $acumuladores['totalOtros']++;
        }

        // Registrar novedad para el detalle
        $acumuladores['novedades'][$concepto->codigo][] = [
            'valor' => $detalle->valor,
            'concepto' => $concepto->codigo.' - '.$concepto->nombre
        ];
    }
    
    private function calcularResultados($acumuladores, $salarioContrato, $metodo)
    {
        if ($metodo == 'false') {
            $salarioDia = $salarioContrato / 30;
            $promedioOtros = $acumuladores['totalHENS'] 
                ? $acumuladores['sumaHENS'] / $acumuladores['totalHENS'] 
                : 0;
        } else {
            $salarioDia = ($acumuladores['sumaSalarios'] / $acumuladores['totalSalarios']) / 12;
            $promedioOtros = $acumuladores['sumaOtros'] / $acumuladores['totalOtros'];
        }

        return [
            'salarioDia' => $salarioDia,
            'promedioOtros' => $promedioOtros,
            'jsonDetalle' => $this->generarJsonDetalle($acumuladores['novedades'], $metodo)
        ];
    }

    private function generarJsonDetalle($novedadesGroup, $metodo)
    {
        return collect($novedadesGroup)
            ->filter(function ($novedades, $codigo) use ($metodo) {
                return $metodo != 'false' || $codigo == '007';
            })
            ->map(function ($novedades, $codigo) {
                $suma = collect($novedades)->sum('valor');
                $total = count($novedades);
                
                return [
                    'concepto' => $novedades[0]['concepto'],
                    'fecha' => now(),
                    'valor' => $suma / $total
                ];
            })
            ->values()
            ->toJson();
    }

}