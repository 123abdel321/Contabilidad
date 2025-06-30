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
//HELPERS
use App\Helpers\Documento;
//TRAITS
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
//MODELS
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;
use App\Models\Sistema\Nomina\NomPrestacionesSociales;
use App\Models\Sistema\Nomina\NomConfiguracionProvisiones;

class PrestacionesSocialesController extends Controller
{
    use BegConsecutiveTrait;
    use BegDocumentHelpersTrait;

    protected $messages = null;
    protected $documento = null;

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

    public function generate(Request $request)
    {
        try {

            if (!$request->get('meses')) {
                return response()->json([
                    "success" => true,
                    'data' => [],
                    "message" => ''
                ], Response::HTTP_OK);
            }

            $fecha = Carbon::parse($request->get('meses'), 'UTC')->addDay();
            $empleados = $this->getEmpleadosParaProvision($fecha);
            $configuraciones = $this->getConfiguracionesProvisiones();
            $provisiones = $this->calcularProvisionesParaEmpleados($empleados, $fecha, $configuraciones);

            return response()->json([
                "success" => true,
                'data' => $provisiones,
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

    public function causar(Request $request)
    {
        $rules = [
            'prestaciones' => 'required|json',
            'fecha' => 'required|date_format:Y-m'
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], HTTP_UNPROCESSABLE_ENTITY);
        }

        $records = json_decode($request->get('prestaciones'));
        $fecha = Carbon::createFromFormat('Y-m', $request->get('fecha'))->lastOfMonth()->startOfDay();
        
        $comprobantePrestacionesSociales = VariablesEntorno::whereNombre("id_comprobante_prestaciones_sociales")->first();
        if(!$comprobantePrestacionesSociales){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> "No esta configurado el comprobante de prestaciones sociales en variables de entorno"
            ], HTTP_UNPROCESSABLE_ENTITY);
        }

        $comprobante = Comprobantes::whereId($comprobantePrestacionesSociales->valor)->first();
        if(!$comprobante){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> "Comprobante configurado no encontrado"
            ], HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::connection('sam')->beginTransaction();

            // Eliminar registros existentes de manera más eficiente
            $empleadosIds = collect($records)->pluck('id_empleado')->unique()->toArray();
            
            DocumentosGeneral::where('id_comprobante', $comprobante->id)
                ->whereDate('fecha_manual', $fecha)
                ->delete();

            NomPrestacionesSociales::whereIn('id_empleado', $empleadosIds)
                ->whereDate('fecha', $fecha)
                ->delete();

            $consecutivo = $this->getNextConsecutive($comprobante, $fecha);
            $this->documento = new Documento((int) $comprobante->id, null, "$fecha", $consecutivo);
            
            // Precargar datos necesarios para optimización
            $contratos = NomContratos::whereIn('id_empleado', $empleadosIds)
                ->where('estado', 1)
                ->with('cecos')
                ->get()
                ->keyBy('id_empleado');

            $errores = [];
            $cuentasCache = [];

            foreach ($records as $record) {
                // Validación básica del registro
                if (empty($record->id_empleado)) {
                    continue;
                }

                // Obtener cuentas con caché local
                $cuentaDeb = explode(" - ", $record->cuenta_debito)[0];
                $cuentaCred = explode(" - ", $record->cuenta_credito)[0];

                if (!isset($cuentasCache[$cuentaDeb])) {
                    $cuentasCache[$cuentaDeb] = PlanCuentas::whereCuenta($cuentaDeb)->first();
                }
                $cuentaDebito = $cuentasCache[$cuentaDeb];

                if (!isset($cuentasCache[$cuentaCred])) {
                    $cuentasCache[$cuentaCred] = PlanCuentas::whereCuenta($cuentaCred)->first();
                }
                $cuentaCredito = $cuentasCache[$cuentaCred];

                // Validar cuentas
                if ((!$cuentaDebito || !$cuentaCredito) && count($errores) <= 4) {
                    $nombreAdministradora = match ($record->id_administradora) {
                        7 => 'Prima',
                        8 => 'Vacaciones',
                        9 => 'Cesantías',
                        10 => 'Intereses cesantías',
                        default => 'Desconocida',
                    };
                    $errores[] = "$nombreAdministradora sin cuenta provisional configurada";
                    continue;
                }

                // Crear provisión
                $provision = NomPrestacionesSociales::create([
                    "id_empleado" => $record->id_empleado,
                    "fecha" => "$fecha 00:00",
                    "concepto" => $record->concepto,
                    "base" => $record->base ?? 0,
                    "porcentaje" => $record->porcentaje ?? 0,
                    "provision" => $record->provision ?? 0,
                    "id_administradora" => $record->id_administradora,
                    "id_cuenta_debito" => $cuentaDebito->id,
                    "id_cuenta_credito" => $cuentaCredito->id,
                    "editado" => $record->editado ?? false,
                    "updated_by" => request()->user()->id,
                    "created_by" => request()->user()->id
                ]);

                // Obtener contrato del empleado
                $contratoEmpleado = $contratos[$record->id_empleado] ?? null;
                if (!$contratoEmpleado) {
                    $errores[] = "No se encontró contrato activo para el empleado ID: {$record->id_empleado}";
                    continue;
                }

                // Crear documentos contables
                $this->createAccountingDocument($cuentaDebito, $record, $contratoEmpleado, false);
                $this->createAccountingDocument($cuentaCredito, $record, $contratoEmpleado, true);
            }

            if (count($errores) > 0) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> "Error al causar prestaciones sociales"
                ], HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!$this->documento->save()) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> "Error al guardar documentos"
                ], HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->updateConsecutivo($comprobante->id, $consecutivo);

            DB::connection('sam')->commit();

            return response()->json([
                "success" => true,
                'data' => [],
                "message" => 'Prestaciones sociales guardadas con éxito'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    protected function getEmpleadosParaProvision(Carbon $fecha)
    {
        return NomPeriodoPagos::select('id_empleado')
            ->whereYear('fecha_fin_periodo', $fecha->format('Y'))
            ->whereMonth('fecha_fin_periodo', $fecha->format('m'))
            ->groupBy('id_empleado')
            ->with('empleado')
            ->get();
    }

    protected function getConfiguracionesProvisiones()
    {
        $configIds = [7 => 'prima', 8 => 'vacaciones', 9 => 'cesantias', 10 => 'intereses_cesantias'];
        $configuraciones = [];

        foreach ($configIds as $id => $tipo) {
            $configuraciones[$tipo] = NomConfiguracionProvisiones::whereId($id)
                ->with('cuenta_administrativos', 'cuenta_operativos', 'cuenta_ventas', 'cuenta_otros', 'cuenta_pagar')
                ->first();
        }

        return $configuraciones;
    }

    protected function calcularProvisionesParaEmpleados($empleados, Carbon $fecha, array $configuraciones)
    {
        $provisiones = [];

        foreach ($empleados as $empleado) {
            $contrato = $this->getContratoValido($empleado->id_empleado);
            if (!$contrato) continue;

            $tipoCuenta = $this->getTipoCuentaEmpleado($contrato->tipo_empleado);
            $periodos = $this->getPeriodosPagoEmpleado($empleado->id_empleado, $fecha);

            $bases = $this->calcularBasesProvision($periodos);
            
            $provisionesEmpleado = [
                $this->crearProvision('PRIMA', $empleado, $contrato, $configuraciones['prima'], $tipoCuenta, $bases['base_prima']),
                $this->crearProvision('VACACIONES', $empleado, $contrato, $configuraciones['vacaciones'], $tipoCuenta, $bases['base_vacacion']),
                $this->crearProvision('CESANTIAS', $empleado, $contrato, $configuraciones['cesantias'], $tipoCuenta, $bases['base_cesantia'], $contrato->fondo_cesantias->descripcion ?? ''),
                $this->crearProvision('INTERESES SOBRE CESANTIAS', $empleado, $contrato, $configuraciones['intereses_cesantias'], $tipoCuenta, $bases['base_interes_cesantia']),
            ];

            $provisiones = array_merge($provisiones, $provisionesEmpleado);
        }

        return $provisiones;
    }

    protected function getContratoValido($idEmpleado)
    {
        return NomContratos::where('id_empleado', $idEmpleado)
            ->where('estado', 1)
            ->with('fondo_cesantias')
            ->first();
    }

    protected function getTipoCuentaEmpleado($tipoEmpleado)
    {
        $tipos = [
            0 => 'cuenta_administrativos',
            1 => 'cuenta_operativos',
            2 => 'cuenta_ventas',
            3 => 'cuenta_otros'
        ];

        return $tipos[$tipoEmpleado] ?? 'cuenta_otros';
    }

    protected function getPeriodosPagoEmpleado($idEmpleado, Carbon $fecha)
    {
        return NomPeriodoPagos::where('id_empleado', $idEmpleado)
            ->whereYear('fecha_fin_periodo', $fecha->format('Y'))
            ->whereMonth('fecha_fin_periodo', $fecha->format('m'))
            ->with('detalles.concepto')
            ->get();
    }

    protected function calcularBasesProvision($periodos)
    {
        $bases = [
            'base_prima' => 0,
            'base_vacacion' => 0,
            'base_cesantia' => 0,
            'base_interes_cesantia' => 0
        ];

        foreach ($periodos as $periodo) {
            foreach ($periodo->detalles as $detalle) {
                if ($detalle->concepto->base_prima) {
                    $bases['base_prima'] += $detalle->valor;
                }
                if ($detalle->concepto->base_vacacion) {
                    $bases['base_vacacion'] += $detalle->valor;
                }
                if ($detalle->concepto->base_cesantia) {
                    $bases['base_cesantia'] += $detalle->valor;
                }
                if ($detalle->concepto->base_interes_cesantia) {
                    $bases['base_interes_cesantia'] += $detalle->valor;
                }
            }
        }

        return $bases;
    }

    protected function crearProvision($concepto, $empleado, $contrato, $config, $tipoCuenta, $base, $fondo = '')
    {
        $salarioIntegral = $contrato->tipo_salario == NomContratos::TIPO_SALARIO_INTEGRAL;
        $porcentaje = $config->porcentaje;
        $provision = $salarioIntegral ? 0 : $base * ($porcentaje / 100);

        return [
            'id_administradora' => $config->id,
            'id_empleado' => $empleado->id_empleado,
            'numero_documento' => $empleado->empleado->numero_documento,
            'empleado' => $empleado->empleado->nombre_completo,
            'concepto' => $concepto,
            'base' => $base,
            'porcentaje' => $porcentaje,
            'provision' => $provision,
            'fondo' => $fondo,
            'cuenta_debito' => $config->{$tipoCuenta} ? $config->{$tipoCuenta}->cuenta.' - '.$config->{$tipoCuenta}->nombre : '',
            'cuenta_credito' => $config->cuenta_pagar ? $config->cuenta_pagar->cuenta.' - '.$config->cuenta_pagar->nombre : '',
            'editado' => false
        ];
    }

    protected function createAccountingDocument($cuenta, $record, $contratoEmpleado, $isCredit)
    {
        $doc = new DocumentosGeneral([
            "id_cuenta" => $cuenta->id,
            "id_nit" => $cuenta->exige_nit ? $record->id_empleado : null,
            "id_centro_costos" => $cuenta->exige_centro_costos ? $contratoEmpleado->cecos->id : null,
            "documento_referencia" => $cuenta->exige_documento_referencia ? $contratoEmpleado->id : '',
            "concepto" => $cuenta->exige_concepto ? $record->concepto : null,
            "debito" => $record->provision,
            "credito" => $record->provision,
        ]);

        $this->documento->addRow($doc, $cuenta->naturaleza_cuenta);
    }
}