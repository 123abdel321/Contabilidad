<?php

namespace App\Http\Controllers\Capturas\Nomina;

use DB;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomNovedadesGenerales;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class LiquidacionDefinitivaController extends Controller
{
    protected $messages = null;
    private const CONCEPTOS = [
        'PRIMA' => 32,
        'VACACIONES' => 33,
        'CESANTIAS' => 30,
        'INTERESES_CESANTIAS' => 31,
        'INDEMNIZACION' => 34, // Asumido, ajustar según tu sistema
        'HORAS_ORDINARIAS' => 1
    ];

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
        return view('pages.capturas.liquidacion_definitiva.liquidacion_definitiva-view');
    }

    public function generate (Request $request)
    {
        try {

            if (!$empleado = $this->validarEmpleado($request)) {
                return response()->json([
                    "success" => true,
                    'data' => [],
                    "message" => ''
                ], Response::HTTP_OK);
            }

            $fechaActual = Carbon::now();
            $ultimoPeriodo = $this->obtenerUltimoPeriodoPago($request->get('id_empleado'));

            $calculos = [
                $this->calcularPrima($empleado, $fechaActual, $request),
                $this->calcularVacaciones($empleado, $ultimoPeriodo, $request),
                $this->calcularCesantias($empleado, $ultimoPeriodo, $request),
                $this->calcularInteresesCesantias($empleado, $ultimoPeriodo, $request),
                $this->calcularIndemnizacion($empleado, $ultimoPeriodo, $fechaActual)
            ];

            return response()->json([
                "success" => true,
                'data' => $calculos,
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

    protected function calcularDiasLaborados($idEmpleado, $fechaInicio, $fechaFin)
    {
        // Obtenemos primero el contrato del empleado con su periodo
        $contrato = NomContratos::with(['periodo' => function($query) {
                $query->select('id', 'horas_dia');
            }])
            ->where('id_empleado', $idEmpleado)
            ->where('estado', 1)
            ->first();

        if (!$contrato || !$contrato->periodo) {
            $horasDia = 8;
        } else {
            $horasDia = $contrato->periodo->horas_dia;
        }

        // Calculamos las horas laboradas
        $horas = NomNovedadesGenerales::where('id_concepto', self::CONCEPTOS['HORAS_ORDINARIAS'])
            ->where('id_empleado', $idEmpleado)
            ->whereHas('periodo_pago', function($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio_periodo', '>=', $fechaInicio)
                    ->where('fecha_fin_periodo', '<=', $fechaFin);
            })
            ->sum('unidades');

        return max($horas, 0) / $horasDia;
    }

    protected function calcularPrima($empleado, $fechaActual, $request)
    {
        $existePrima = NomNovedadesGenerales::where('id_concepto', self::CONCEPTOS['PRIMA'])
            ->where('id_empleado', $request->get('id_empleado'))
            ->whereYear('fecha_fin', $fechaActual->format('Y'))
            ->exists();

        if ($existePrima) {
            $fechaInicio = Carbon::parse($fechaActual->format('Y').'-07-01')->subDay();
            $fechaFin = Carbon::parse($fechaActual->format('Y').'-12-31')->subDay();
        } else {
            $mes = explode('-', $empleado->contrato->fecha_inicio_contrato)[1] < 7 ? '-07-01' : '-12-31';
            $fechaInicio = Carbon::parse($empleado->contrato->fecha_inicio_contrato)->subDay();
            $fechaFin = Carbon::parse($fechaActual->format('Y').$mes)->subDay();
        }

        $devengados = $this->getDevengados($request->get('id_empleado'), $fechaInicio, $fechaFin, 'prima');
        $dias = $this->calcularDiasLaborados($request->get('id_empleado'), $fechaInicio, $fechaFin);
        
        return [
            'id_empleado' => $empleado->empleado->id,
            'id_concepto' => NomConceptos::whereTipoConcepto('primas')->first()->id,
            'empleado' => $empleado->empleado->nombre_completo,
            'numero_documento' => $empleado->empleado->numero_documento,
            'concepto' => 'Prima',
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'dias' => $dias,
            'base' => $devengados,
            'promedio' => $dias ? $devengados / $dias : 0,
            'total' => $dias ? $devengados * $dias / 360 : 0,
            'observacion' => ''
        ];
    }

    protected function calcularVacaciones($empleado, $ultimoPeriodo, $request)
    {
        $existeVacacion = NomNovedadesGenerales::where('id_concepto', self::CONCEPTOS['VACACIONES'])
            ->where('id_empleado', $request->get('id_empleado'))
            ->latest('created_at')
            ->first();

        $fechaInicio = $existeVacacion ? $existeVacacion->fecha_fin : $empleado->contrato->fecha_inicio_contrato;
        $fechaFin = $ultimoPeriodo->fecha_fin_periodo;

        $dias = $this->calcularDiasLaborados($request->get('id_empleado'), $fechaInicio, $fechaFin);

        return [
            'id_empleado' => $empleado->empleado->id,
            'id_concepto' => NomConceptos::whereTipoConcepto('vacaciones_comunes')->first()->id,
            'empleado' => $empleado->empleado->nombre_completo,
            'numero_documento' => $empleado->empleado->numero_documento,
            'concepto' => 'Vacaciones',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'dias' => $dias,
            'base' => $empleado->contrato->salario,
            'promedio' => $dias ? $empleado->contrato->salario / $dias : 0,
            'total' => $dias ? ($empleado->contrato->salario * $dias) / 720 : 0,
            'observacion' => ''
        ];
    }

    protected function calcularCesantias($empleado, $ultimoPeriodo, $request)
    {
        $existeCesantias = NomNovedadesGenerales::where('id_concepto', self::CONCEPTOS['CESANTIAS'])
            ->where('id_empleado', $request->get('id_empleado'))
            ->latest('created_at')
            ->first();

        $fechaInicio = $existeCesantias ? $existeCesantias->fecha_fin : $empleado->contrato->fecha_inicio_contrato;
        $fechaFin = $ultimoPeriodo->fecha_fin_periodo;

        $devengados = $this->getDevengados($request->get('id_empleado'), $fechaInicio, $fechaFin, 'cesantia');
        $dias = $this->calcularDiasLaborados($request->get('id_empleado'), $fechaInicio, $fechaFin);

        return [
            'id_empleado' => $empleado->empleado->id,
            'id_concepto' => 30,
            'empleado' => $empleado->empleado->nombre_completo,
            'numero_documento' => $empleado->empleado->numero_documento,
            'concepto' => 'Cesantías',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'dias' => $dias,
            'base' => $devengados,
            'promedio' => $dias ? $devengados / $dias : 0,
            'total' => $dias ? ($devengados * $dias) / 360 : 0,
            'observacion' => ''
        ];
    }

    protected function calcularInteresesCesantias($empleado, $ultimoPeriodo, $request)
    {
        $existeIntereses = NomNovedadesGenerales::where('id_concepto', self::CONCEPTOS['INTERESES_CESANTIAS'])
            ->where('id_empleado', $request->get('id_empleado'))
            ->latest('created_at')
            ->first();

        $fechaInicio = $existeIntereses ? $existeIntereses->fecha_fin : $empleado->contrato->fecha_inicio_contrato;
        $fechaFin = $ultimoPeriodo->fecha_fin_periodo;

        $devengados = $this->getDevengados($request->get('id_empleado'), $fechaInicio, $fechaFin, 'interes_cesantia');
        $dias = $this->calcularDiasLaborados($request->get('id_empleado'), $fechaInicio, $fechaFin);
        $cesantiasValor = $dias ? ($devengados * $dias) / 360 : 0;

        return [
            'id_empleado' => $empleado->empleado->id,
            'id_concepto' => NomConceptos::whereTipoConcepto('cesantias')->first()->id,
            'empleado' => $empleado->empleado->nombre_completo,
            'numero_documento' => $empleado->empleado->numero_documento,
            'concepto' => 'Intereses Cesantías',
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'dias' => $dias,
            'base' => $cesantiasValor,
            'promedio' => $dias ? $cesantiasValor / $dias : 0,
            'total' => $dias ? ($cesantiasValor * $dias * 0.12) / 360 : 0,
            'observacion' => ''
        ];
    }

    protected function calcularIndemnizacion($empleado, $ultimoPeriodo, $fechaActual)
    {
        $fechaInicio = $fechaActual->copy()->startOfYear();
        $fechaFin = Carbon::parse($ultimoPeriodo->fecha_fin_periodo);
        $fechaContrato = Carbon::parse($empleado->contrato->fecha_inicio_contrato);
        
        $diasIndemnizacion = $this->calcularDiasIndemnizacion(
            $empleado->contrato,
            $fechaContrato,
            $fechaFin
        );

        if ($diasIndemnizacion <= 0) {
            return null;
        }

        $devengados = $this->getDevengados(
            $empleado->contrato->id_empleado,
            $fechaInicio->format('Y-m-d'),
            $fechaFin->format('Y-m-d'),
            'salud'
        );

        $promedioDiario = $diasIndemnizacion ? $devengados / $diasIndemnizacion : 0;
        $total = $diasIndemnizacion ? ($promedioDiario / 30) * $diasIndemnizacion : 0;

        return [
            'id_empleado' => $empleado->empleado->id,
            'id_concepto' => NomConceptos::whereTipoConcepto('indemnizacion')->first()->id,
            'empleado' => $empleado->empleado->nombre_completo,
            'numero_documento' => $empleado->empleado->numero_documento,
            'concepto' => 'Indemnización',
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'dias' => $diasIndemnizacion,
            'base' => $devengados,
            'promedio' => $promedioDiario,
            'total' => $total,
            'observacion' => '',
        ];
    }

    protected function calcularDiasIndemnizacion($contrato, $fechaInicioContrato, $fechaFin)
    {
        $salarioMinimo = VariablesEntorno::whereNombre('salario_minimo')->first()->valor;
        $diasTotales = $fechaInicioContrato->diffInDays($fechaFin);
        $diasIndemnizacion = 0;

        if ($contrato->termino == NomContratos::TIPO_TERMINO_INDEFINIDO) {
            $diasBase = ($salarioMinimo * 10 > $contrato->salario) ? 30 : 20;
            $diasIncremento = ($salarioMinimo * 10 > $contrato->salario) ? 20 : 15;
            
            $diasIndemnizacion = $diasBase;
            if ($diasTotales > 360) {
                $diasRestantes = $diasTotales - 360;
                $diasIndemnizacion += ($diasRestantes / 360) * $diasIncremento;
            }
        } else if ($contrato->termino == NomContratos::TIPO_TERMINO_FIJO) {
            $diasIndemnizacion = $fechaInicioContrato > $fechaFin->copy()->startOfYear() 
                ? $fechaInicioContrato->diffInDays($contrato->fecha_fin_contrato)
                : $fechaFin->copy()->startOfYear()->diffInDays($contrato->fecha_fin_contrato);
        }

        return max(ceil($diasIndemnizacion), 0);
    }

    protected function validarEmpleado(Request $request)
    {
        $empleado = Nits::find($request->get('id_empleado'));
        $contrato = NomContratos::where('id_empleado', $request->get('id_empleado'))
            ->where('estado', 1)
            ->first();

        return $empleado && $contrato ? (object)['empleado' => $empleado, 'contrato' => $contrato] : null;
    }

    protected function obtenerUltimoPeriodoPago($idEmpleado)
    {
        return NomPeriodoPagos::where('id_empleado', $idEmpleado)
            ->orderBy('id', 'DESC')
            ->first();
    }

    protected function getDevengados($idEmpleado, $fechaInicio, $fechaFin, $tipo = null)
    {
        $detallePeriodoPago = NomPeriodoPagoDetalles::select([
            '*',
            DB::raw('IF (valor >= 0, valor, 0) AS devengados'),
            DB::raw('IF (valor < 0, valor, 0) AS deducciones'),
        ])
        ->with(['periodoPago', 'concepto'])
        ->whereHas('periodoPago', function ($q) use ($idEmpleado, $fechaInicio, $fechaFin) {
            $q->where('id_empleado', $idEmpleado);
            $q->where('fecha_inicio_periodo', '>=', $fechaInicio);
            $q->where('fecha_fin_periodo', '<=', $fechaFin);
        })
        ->whereHas('concepto', function($q) use($tipo) {
            if($tipo == 'prima'){
                $q->where('base_prima', 1);
            } else if ($tipo == 'vacacion') {
                $q->where('base_vacacion', 1);
            } else if ($tipo == 'cesantia') {
                $q->where('base_cesantia', 1);
            } else if ($tipo == 'interes_cesantia') {
                $q->where('base_interes_cesantia', 1);
            } else if ($tipo == 'salud') {
                $q->where('base_salud', 1);
            }
        })
        ->get();
        
        if($tipo) return $detallePeriodoPago->sum('valor');

        return $detallePeriodoPago->sum('devengados');
    }

}