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
use App\Models\Sistema\Nits;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomParafiscales;
use App\Models\Sistema\Nomina\NomAdministradoras;
use App\Models\Sistema\Nomina\NomSeguridadSocial;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;
use App\Models\Sistema\Nomina\NomPrestacionesSociales;
use App\Models\Sistema\Nomina\NomConfiguracionProvisiones;

class CausarProvicionadaController extends Controller
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
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
    }

    public function generatePrestaciones(Request $request)
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
            $configuraciones = $this->getConfiguracionesPrestasionesSociales();
            $provisiones = $this->calcularPrestasionesSocialesParaEmpleados($empleados, $fecha, $configuraciones);

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

    public function generateSeguridad(Request $request)
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
            $configuraciones = $this->getConfiguracionesSeguridadSocial();
            $provisiones = $this->calcularSeguridadSocialParaEmpleados($empleados, $fecha, $configuraciones);

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

    public function generateParafiscales(Request $request)
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
            $configuraciones = $this->getConfiguracionesParafiscales();

            $provisiones = $this->calcularParafiscalesParaEmpleados(
                $empleados,
                $fecha,
                $configuraciones
            );

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

    public function causarPrestaciones(Request $request)
    {
        return $this->causarProvisiones($request, 'prestaciones');
    }

    public function causarSeguridad(Request $request)
    {
        return $this->causarProvisiones($request, 'seguridad');
    }

    public function causarParafiscales(Request $request)
    {
        return $this->causarProvisiones($request, 'parafiscales');
    }

    public function causarProvisiones(Request $request, string $tipo)
    {
        $rules = [
            'prestaciones' => 'required|json',
            'fecha' => 'required|date_format:Y-m'
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Configuración según el tipo de provisión
        $config = $this->getConfigByTipo($tipo);
        if (!$config['success']) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $config['message']
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $records = json_decode($request->get('prestaciones'));
        $fecha = Carbon::createFromFormat('Y-m', $request->get('fecha'))->lastOfMonth()->startOfDay();

        try {
            DB::connection('sam')->beginTransaction();

            // Eliminar registros existentes
            $empleadosIds = collect($records)->pluck('id_empleado')->unique()->toArray();
            
            DocumentosGeneral::where('id_comprobante', $config['comprobante']->id)
                ->whereDate('fecha_manual', $fecha)
                ->delete();

            $config['model']::whereIn('id_empleado', $empleadosIds)
                ->whereDate('fecha', $fecha)
                ->delete();

            $consecutivo = $this->getNextConsecutive($config['comprobante'], $fecha);
            $this->documento = new Documento((int) $config['comprobante']->id, null, "$fecha", $consecutivo);
            
            // Precargar datos necesarios
            $contratos = NomContratos::whereIn('id_empleado', $empleadosIds)
                ->where('estado', 1)
                ->with('cecos', 'nit')
                ->get()
                ->keyBy('id_empleado');

            $errores = [];
            $cuentasCache = [];

            foreach ($records as $record) {
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
                if ((!$cuentaDebito || !$cuentaCredito)) {
                    $nombreAdministradora = $this->getNombreAdministradora($tipo, $record->id_administradora);
                    $errores[$nombreAdministradora] = ["sin cuentas contables configuradas"];
                    continue;
                }

                // Crear provisión
                $provision = $config['model']::create([
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
                    $nit = Nits::where('id', $record->id_empleado)->first();
                    $errores["Empleado: {$nit->nombre_completo}"] = ["No se encontró contrato activo"];
                    continue;
                }

                $idNitAdministradora = $this->getAdministradoraNitId($record->id_administradora);
                
                // Crear documentos contables
                $this->createAccountingDocument(
                    $cuentaDebito,
                    $record,
                    $contratoEmpleado,
                    $idNitAdministradora,
                    false
                );

                $this->createAccountingDocument(
                    $cuentaCredito,
                    $record,
                    $contratoEmpleado,
                    $idNitAdministradora,
                    true
                );
            }

            if (count($errores) > 0) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $errores
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!$this->documento->save()) {
                DB::connection('sam')->rollback();

                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $this->documento->getErrors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->updateConsecutivo($config['comprobante']->id, $consecutivo);

            DB::connection('sam')->commit();

            return response()->json([
                "success" => true,
                'data' => [],
                "message" => $config['success_message']
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    protected function getConfigByTipo(string $tipo): array
    {
        $config = [
            'prestaciones' => [
                'variable' => 'id_comprobante_prestaciones_sociales',
                'model' => NomPrestacionesSociales::class,
                'success_message' => 'Prestaciones sociales guardadas con éxito',
                'error_message' => 'No esta configurado el comprobante de prestaciones sociales en variables de entorno'
            ],
            'seguridad' => [
                'variable' => 'id_comprobante_seguridad_social',
                'model' => NomSeguridadSocial::class,
                'success_message' => 'Seguridades sociales guardadas con éxito',
                'error_message' => 'No esta configurado el comprobante de seguridad social en variables de entorno'
            ],
            'parafiscales' => [
                'variable' => 'id_comprobante_parafiscales',
                'model' => NomParafiscales::class,
                'success_message' => 'Parafiscales guardadas con éxito',
                'error_message' => 'No esta configurado el comprobante de parafiscales en variables de entorno'
            ]
        ];

        if (!array_key_exists($tipo, $config)) {
            return ['success' => false, 'message' => 'Tipo de provisión no válido'];
        }

        $comprobanteVariable = VariablesEntorno::whereNombre($config[$tipo]['variable'])->first();
        if (!$comprobanteVariable) {
            return ['success' => false, 'message' => $config[$tipo]['error_message']];
        }

        $comprobante = Comprobantes::whereId($comprobanteVariable->valor)->first();
        if (!$comprobante) {
            return ['success' => false, 'message' => "Comprobante configurado no encontrado"];
        }

        return [
            'success' => true,
            'comprobante' => $comprobante,
            'model' => $config[$tipo]['model'],
            'success_message' => $config[$tipo]['success_message']
        ];
    }

    protected function getNombreAdministradora(string $tipo, int $idAdministradora): string
    {
        $map = [
            'prestaciones' => [
                7 => 'Prima',
                8 => 'Vacaciones',
                9 => 'Cesantías',
                10 => 'Intereses cesantías',
            ],
            'seguridad' => [
                4 => 'Salud',
                5 => 'Pensión',
                6 => 'Arl',
            ],
            'parafiscales' => [
                4 => 'Caja compensación',
                5 => 'Icbf',
                6 => 'Sena',
            ]
        ];

        return $map[$tipo][$idAdministradora] ?? 'Desconocida';
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

    protected function getConfiguracionesPrestasionesSociales()
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

    protected function getConfiguracionesSeguridadSocial()
    {
        $configIds = [4 => 'salud', 5 => 'pension', 6 => 'arl'];
        $configuraciones = [];

        foreach ($configIds as $id => $tipo) {
            $configuraciones[$tipo] = NomConfiguracionProvisiones::whereId($id)
                ->with('cuenta_administrativos', 'cuenta_operativos', 'cuenta_ventas', 'cuenta_otros', 'cuenta_pagar')
                ->first();
        }

        return $configuraciones;
    }

    protected function getConfiguracionesParafiscales()
    {
        $configIds = [1 => 'caja_compensacion', 2 => 'icbf', 3 => 'sena'];
        $configuraciones = [];

        foreach ($configIds as $id => $tipo) {
            $configuraciones[$tipo] = NomConfiguracionProvisiones::whereId($id)
                ->with('cuenta_administrativos', 'cuenta_operativos', 'cuenta_ventas', 'cuenta_otros', 'cuenta_pagar')
                ->first();
        }

        return $configuraciones;
    }

    protected function getAdministradoraNitId($id_administradora)
    {
        $administradoras = NomAdministradoras::with('nit')
            ->where('id', $id_administradora)
            ->first();

        return $administradoras->nit?->id;
    }

    protected function calcularPrestasionesSocialesParaEmpleados($empleados, Carbon $fecha, array $configuraciones)
    {
        $provisiones = [];

        foreach ($empleados as $empleado) {
            $contrato = $this->getContratoValido($empleado->id_empleado);
            if (!$contrato) continue;

            $tipoCuenta = $this->getTipoCuentaEmpleado($contrato->tipo_empleado);
            $periodos = $this->getPeriodosPagoEmpleado($empleado->id_empleado, $fecha);
            $bases = $this->calcularBasesPrestacionesSociales($periodos);
            
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

    protected function calcularSeguridadSocialParaEmpleados($empleados, Carbon $fecha, array $configuraciones)
    {
        $provisiones = [];

        foreach ($empleados as $empleado) {
            $contrato = $this->getContratoValido($empleado->id_empleado);
            if (!$contrato) continue;

            $salarioMinimo = VariablesEntorno::whereNombre("salario_minimo")->first()?->valor;
            $noExoneradoDeParafiscales = VariablesEntorno::whereNombre('no_exonerado_parafiscales')->first()?->valor;

            $tipoCuenta = $this->getTipoCuentaEmpleado($contrato->tipo_empleado);
            $periodos = $this->getPeriodosPagoEmpleado($empleado->id_empleado, $fecha);
            $bases = $this->calcularBasesSeguridadSocial(
                $periodos,
                $contrato,
                $salarioMinimo,
                $noExoneradoDeParafiscales
            );
            
            $provisionesEmpleado = [
                $this->crearProvision('ARL', $empleado, $contrato, $configuraciones['arl'], $tipoCuenta, $bases['base_arl'], $contrato->fondo_arl->descripcion ?? ''),
                $this->crearProvision('SALUD', $empleado, $contrato, $configuraciones['salud'], $tipoCuenta, $bases['base_salud'], $contrato->fondo_salud->descripcion ?? ''),
                $this->crearProvision('PENSIÓN', $empleado, $contrato, $configuraciones['pension'], $tipoCuenta, $bases['base_pension'], $contrato->fondo_pension->descripcion ?? ''),
            ];

            $provisiones = array_merge($provisiones, $provisionesEmpleado);
        }

        return $provisiones;
    }

    protected function calcularParafiscalesParaEmpleados($empleados, Carbon $fecha, array $configuraciones)
    {
        $provisiones = [];

        foreach ($empleados as $empleado) {
            $contrato = $this->getContratoValido($empleado->id_empleado);
            if (!$contrato) continue;

            $salarioMinimo = VariablesEntorno::whereNombre("salario_minimo")->first()?->valor;
            $noExoneradoDeParafiscales = VariablesEntorno::whereNombre('no_exonerado_parafiscales')->first()?->valor;

            $tipoCuenta = $this->getTipoCuentaEmpleado($contrato->tipo_empleado);
            $periodos = $this->getPeriodosPagoEmpleado($empleado->id_empleado, $fecha);

            $bases = $this->calcularBasesParafiscales(
                $periodos,
                $contrato,
                $salarioMinimo,
                $noExoneradoDeParafiscales
            );
            
            $provisionesEmpleado = [
                $this->crearProvision('ICBF', $empleado, $contrato, $configuraciones['icbf'], $tipoCuenta, $bases['base_icbf'], ''),
                $this->crearProvision('SENA', $empleado, $contrato, $configuraciones['sena'], $tipoCuenta, $bases['base_sena'], ''),
                $this->crearProvision('CAJA COMPENSACION', $empleado, $contrato, $configuraciones['caja_compensacion'], $tipoCuenta, $bases['base_caja_compensacion'], $contrato->fondo_caja_compensacion->descripcion ?? ''),
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

    protected function calcularBasesPrestacionesSociales($periodos)
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
                    $bases['base_prima']+= $detalle->valor;
                }
                if ($detalle->concepto->base_vacacion) {
                    $bases['base_vacacion']+= $detalle->valor;
                }
                if ($detalle->concepto->base_cesantia) {
                    $bases['base_cesantia']+= $detalle->valor;
                }
                if ($detalle->concepto->base_interes_cesantia) {
                    $bases['base_interes_cesantia']+= $detalle->valor;
                }
            }
        }

        return $bases;
    }

    protected function calcularBasesSeguridadSocial($periodos, $contrato, $salarioMinimo, $noExoneradoDeParafiscales)
    {
        $bases = [
            'base_arl' => 0,
            'base_salud' => 0,
            'base_pension' => 0
        ];

        foreach ($periodos as $periodo) {
            foreach ($periodo->detalles as $detalle) {
                if ($detalle->concepto->base_arl) {
                    $bases['base_arl']+= $detalle->valor;
                }
                if ($detalle->concepto->base_salud) {
                    if ($contrato->tipo_salario == NomContratos::TIPO_SALARIO_INTEGRAL ||
                        $contrato->salario > intval($salarioMinimo) * 10 ||
                        $noExoneradoDeParafiscales) {
                        $bases['base_salud']+= $detalle->valor;
                    }
                }
                if ($detalle->concepto->base_pension) {
                    $bases['base_pension']+= $detalle->valor;
                }
            }
        }

        return $bases;
    }

    protected function calcularBasesParafiscales($periodos, $contrato, $salarioMinimo, $noExoneradoDeParafiscales)
    {
        $bases = [
            'base_icbf' => 0,
            'base_sena' => 0,
            'base_caja_compensacion' => 0
        ];

        foreach ($periodos as $periodo) {
            foreach ($periodo->detalles as $detalle) {
                $baseParafiscales = $contrato->tipo_salario == NomContratos::TIPO_SALARIO_INTEGRAL ? 
                    $detalle->valor * 0.7 :
                    $detalle->valor;
                    
                if ($detalle->concepto->base_icbf) {
                    $bases['base_icbf']+= $baseParafiscales;
                }
                if ($detalle->concepto->base_sena) {
                    $bases['base_sena']+= $baseParafiscales;
                }
                if ($detalle->concepto->base_caja_compensacion) {
                    $bases['base_caja_compensacion']+= $baseParafiscales;
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

    protected function createAccountingDocument(
        $cuenta,
        $record,
        $contratoEmpleado,
        $idNitAdministradora,
        $isCredit
    ) {
        $doc = new DocumentosGeneral([
            "id_cuenta" => $cuenta->id,
            "id_nit" => $cuenta->exige_nit ? $idNitAdministradora : null,
            "id_centro_costos" => $cuenta->exige_centro_costos ? $contratoEmpleado->cecos->id : null,
            "documento_referencia" => $cuenta->exige_documento_referencia ? $contratoEmpleado->id : '',
            "concepto" => $cuenta->exige_concepto ? $record->concepto : null,
            "debito" => $record->provision,
            "credito" => $record->provision,
        ]);

        $this->documento->addRow($doc, $cuenta->naturaleza_cuenta);
    }
}