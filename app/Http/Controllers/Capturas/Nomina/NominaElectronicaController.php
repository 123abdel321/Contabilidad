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
use App\Helpers\NominaElectronica\NominaElectronicaSender;
//TRAITS
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\FacDocumentos;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomElectronica;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomNovedadesGenerales;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;


class NominaElectronicaController extends Controller
{
    use BegConsecutiveTrait;
	use BegDocumentHelpersTrait;

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
        return view('pages.capturas.nomina_electronica.nomina_electronica-view');
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

            $nominaEmpleados = Nits::where('empleado', 1)
                ->has('contrato_actual')
                ->with(['electronica' => function ($query) use($mes) {
                    $query->where('mes', $mes->format('Y-m'));
                    $query->orderBy('id', 'DESC');
                }]);

            $totalNominaEmpleados = $nominaEmpleados->count();
            
            $nominaElectronicaPaginate = $nominaEmpleados->skip($start)
                ->take($rowperpage);
    
            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $totalNominaEmpleados,
                'iTotalDisplayRecords' => $totalNominaEmpleados,
                'data' => $nominaElectronicaPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Nomina electronica empleados con exito!'
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

    public function enviar (Request $request)
    {
        $rules = [
            "id_empleado" => "required|exists:sam.nits,id",
            "fecha" => "required"
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {

            $fecha = Carbon::parse($request->get('fecha'), 'UTC');
            $empleado = Nits::where('id', $request->get('id_empleado'))
                ->with('contrato_actual.periodo')
                ->firstOrFail();

            if (!$empleado->contrato_actual?->periodo) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> 'Contrato o periodo no encontrado'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            // 2. VERIFICAR PERIODOS PAGADOS
            $totalPeriodos = count(explode(",", $empleado->contrato_actual->periodo->periodo_dias_ordinales));
            $periodosPagos = NomPeriodoPagos::where('id_empleado', $empleado->id)
                ->whereYear('fecha_fin_periodo', $fecha->year)
                ->whereMonth('fecha_fin_periodo', $fecha->month)
                ->where('estado', '!=', 0);

            if ($totalPeriodos !== $periodosPagos->count()) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> 'SIN DATOS: Periodos incompletos'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            // 3. VERIFICAR NÓMINAS EXISTENTES
            $nominaMes = NomElectronica::where('id_empleado', $empleado->id)
                ->where('mes', $fecha->format('Y-m'))
                ->get();
            
            // 4. CASO 1: NO EXISTE NOMINA PARA EL MES
            if ($nominaMes->isEmpty()) {

                $nominaElectronica = NomElectronica::create([
                    'id_empleado' => $empleado->id,
                    'cune' => 'PENDIENTE',
                    'mes' => $fecha->format('Y-m'),
                    'tipo' => NomElectronica::TIPO_INDIVIDUAL,
                ]);

                $responseNE = (new NominaElectronicaSender(
                    $empleado->id, 
                    $periodosPagos->pluck('id')->toArray(), 
                    $nominaElectronica->id
                ))->send();
                
                return $this->validateResponse($nominaElectronica, $empleado, $responseNE);
            }

            // 5. CASO 2: EXISTE NOMINA INDIVISUAL PENDIENTE
            $nominaPendiente = $nominaMes->where('cune', 'PENDIENTE')
                ->where('tipo', NomElectronica::TIPO_INDIVIDUAL)
                ->first();
            
            if ($nominaPendiente) {
                $responseNE = (new NominaElectronicaSender(
                    $empleado->id, 
                    $periodosPagos->pluck('id')->toArray(), 
                    $nominaPendiente->id
                ))->send();
                
                return $this->validateResponse($nominaPendiente, $empleado, $responseNE);
            }

            // 6. CASO 3: BUSCAR NOMINA INDIVISUAL PROCESADA
            $nominaProcesada = $nominaMes->where('cune', '!=', 'PENDIENTE')
                ->where('tipo', NomElectronica::TIPO_INDIVIDUA)
                ->first();

            // 7. CASO 4: BUSCAR AJUSTE PENDEINTE
            $ajustePendiente = NomElectronica::where('id_empleado', $empleado->id)
                ->where('cune', 'PENDIENTE')
                ->where('mes', $fecha->format('Y-m'))
                ->where('tipo', NomElectronica::TIPO_INDIVIDUAL_AJUSTE)
                ->orderBy('id', 'DESC')
                ->first();

            if ($ajustePendiente) {
                $responseNE = (new NominaElectronicaAjusteSender(
                    $empleado->id, 
                    $periodosPagos->pluck('id')->toArray(), 
                    $ajustePendiente->id, 
                    $nominaProcesada
                ))->send();
                
                return $this->validateResponse($ajustePendiente, $empleado, $responseNE);
            }

            // 8. CASO 5: CREAR NUEVO AJUSTE
            $nuevoAjuste = NomElectronica::create([
                'id_empleado' => $empleado->id,
                'cune' => 'PENDIENTE',
                'mes' => $fecha->format('Y-m'),
                'tipo' => '1',
            ]);
            
            $responseNE = (new NominaElectronicaAjusteSender(
                $empleado->id, 
                $periodosPagos->pluck('id')->toArray(), 
                $nuevoAjuste->id, 
                $nominaProcesada
            ))->send();
            
            return $this->validateResponse($nuevoAjuste, $empleado, $responseNE);

        } catch (\Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function validateResponse($nominaElectronica, $empleado, $response)
    {
        if (!$response) {
            return response()->json([
                "success" => true,
                "mensaje" => 'SIN DATOS'
            ], Response::HTTP_OK);
        }

        if (isset($response['status']) && $response['status'] < 300) {
            $nominaElectronica->cune = $response['cune'] ?? 'PENDIENTE';
            $nominaElectronica->save();
            
            return response()->json([
                "success" => true,
                "mensaje" => 'ENVIADO CON ÉXITO',
                "json" => $response['params'] ?? null,
                "response" => $response['response'] ?? null,
                "cune" => $response['cune'] ?? null
            ], Response::HTTP_OK);
        }

        return response()->json([
            "success" => false,
            "mensaje" => 'ERROR AL ENVIAR',
            "errores" => [
                "empleado" => $empleado->nombre_completo ?? 'Desconocido',
                "message" => $response['message'] ?? 'Error desconocido',
                "status_code" => $response['status'] ?? 500,
                "errors" => $response['errors'] ?? null,
                "json" => $response['json'] ?? $response['params'] ?? null
            ]
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}