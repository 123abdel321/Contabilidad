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
//HELPER
use App\Helpers\Documento;
//TRAITS
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

class CausarNominaController extends Controller
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
            
            $mes = CarbonImmutable::parse($request->get('mes'));
            $idsPeriodoPago = $request->get('id');
            $fechasPeriodos = (new CalcularPeriodo())->calcularNominas($mes->format('Y-m'), null, $idsPeriodoPago);

            return response()->json([
                "success"=> true,
                'data' => $fechasPeriodos,
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

    public function causarNomina(Request $request)
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

        // Validar configuración inicial
        $configCuentaXPagarEmpleado = VariablesEntorno::where('nombre', 'cuenta_x_pagar_empleados')->first();
        $cuentaXPagarEmpleado = PlanCuentas::where('cuenta', $configCuentaXPagarEmpleado ? $configCuentaXPagarEmpleado->valor : null)->first();

        if (!$cuentaXPagarEmpleado) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> 'La cuenta x pagar empleados no está configurada correctamente.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $configComprobanteNomina = VariablesEntorno::where('nombre', "id_comprobante_nomina")
            ->pluck('valor')
            ->first();

        $comprobante = Comprobantes::where('id', $configComprobanteNomina)->first();

        if (!$comprobante) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> 'El comprobante de nómina no existe o no está configurado correctamente'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::connection('sam')->beginTransaction();

        try {

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

            foreach ($periodosPago as $periodoPago) {
                // Validar estado del período
                if ($periodoPago->estado !== NomPeriodoPagos::ESTADO_PENDIENTE) {
                    $errorTitle = $periodoPago->empleado->nombre_completo;
                    $errors[$errorTitle] = ['periodo_pago' => "El periodo seleccionado $periodoPago->fecha_inicio_periodo - $periodoPago->fecha_fin_periodo ya se encuentra causado."];
                    continue;
                }

                // Procesar el período
                $processResult = $this->processSinglePeriodo(
                    $periodoPago,
                    $cuentaXPagarEmpleado,
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
                "message" => 'Causación realizada con exito'
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

    public function descausarNomina(Request $request)
    {
        $rules = [
            'ids_periodos_pago' => 'required|array',
            'ids_periodos_pago.*' => 'exists:sam.nom_periodo_pagos,id'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::connection('sam')->beginTransaction();

        try {
            $periodosPago = NomPeriodoPagos::with('documentos')
                ->whereIn('id', $request->get('ids_periodos_pago'))
                ->get();

            $errors = [];
            $processedCount = 0;

            foreach ($periodosPago as $periodoPago) {
                // Validar estado del período
                if ($periodoPago->estado !== NomPeriodoPagos::ESTADO_CAUSADO) {
                    $errors[] = "El período {$periodoPago->fecha_inicio_periodo} - {$periodoPago->fecha_fin_periodo} del empleado {$periodoPago->empleado->nombre_completo} no está causado.";
                    continue;
                }

                // Eliminar documentos relacionados si existen
                if ($periodoPago->documentos && $periodoPago->documentos->count() > 0) {
                    $periodoPago->documentos()->delete();
                }

                // Actualizar estado
                $periodoPago->estado = NomPeriodoPagos::ESTADO_PENDIENTE;
                $periodoPago->save();

                $processedCount++;
            }

            // Si hay errores en algún período, hacer rollback completo
            if (!empty($errors)) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $errors
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::connection('sam')->commit();

            return response()->json([
                "success" => true,
                'data' => [],
                "message" => 'Descausación realizada con éxito'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function processSinglePeriodo($periodoPago, $cuentaXPagarEmpleado, $comprobante)
    {
        $errors = [];
        $contrato = $periodoPago->contrato;
        $fecha = $periodoPago->fecha_fin_periodo;
        $consecutivo = $this->getNextConsecutive($comprobante, $fecha);
        $empleado = $periodoPago->empleado;

        $datosConcepto =  'Periodo ' . $periodoPago->fecha_fin_periodo . ' / ' . $empleado->numero_documento_completo . ' ' . $empleado->nombre_completo;
        
        $documentoGeneral = new Documento(
            $comprobante->id,
            $periodoPago,
            $fecha,
            $consecutivo,
            false
        );

        $documentoGeneral->setConceptoDefault('NÓMINA GENERADA POR EL SISTEMA');

        // Calcular valor neto
        $valorNeto = $periodoPago->detalles->sum('valor');

        // Agregar cuenta por pagar empleado
        $rowCuentaXPagarEmpleado = new DocumentosGeneral([
            "id_cuenta" => $cuentaXPagarEmpleado->id,
            "id_nit" => $contrato->id_empleado,
            "id_centro_costos" => $contrato->id_centro_costo,
            "documento_referencia" => $fecha,
            "concepto" => '',
            "debito" => 0,
            "credito" => $valorNeto,
        ]);

        $documentoGeneral->addRow($rowCuentaXPagarEmpleado, PlanCuentas::CREDITO);
        
        // Procesar detalles
        foreach ($periodoPago->detalles as $detallePeriodoPago) {
            $concepto = $detallePeriodoPago->concepto;
            $relacionCuentaTipoEmpleado = 'cuenta_' . NomContratos::TIPOS_EMPLEADO[$contrato->tipo_empleado];
            
            $cuenta = $concepto->{$relacionCuentaTipoEmpleado};
            
            if (!$cuenta) {
                $tipoEmpleadoLabel = str_replace('_', ' de ', $relacionCuentaTipoEmpleado);
                $errors['Tablas <b style="color: #000">→</b> Concepto de nomina'] = ['cuenta' => " <b style='color: #481897'>{$concepto->codigo}</b> - <b style='color: #481897'>{$concepto->nombre}</b> no tiene <b style='color: #481897'>{$tipoEmpleadoLabel}</b> asociada."];
                continue;
            }

            // Determinar NIT según el concepto
            $idNit = $this->determineNitForConcepto($concepto, $contrato, $empleado);
            if (isset($idNit['error'])) {
                $errors = array_merge($errors, $idNit['error']);
                continue;
            }

            $row = new DocumentosGeneral([
                "id_cuenta" => $cuenta->id,
                "id_nit" => $idNit['id_nit'],
                "id_centro_costos" => $contrato->id_centro_costo,
                "documento_referencia" => $fecha,
                "concepto" => $concepto->nombre . ' / ' . $datosConcepto,
                "debito" => $cuenta->naturaleza_cuenta === PlanCuentas::DEBITO ? abs($detallePeriodoPago->valor) : 0,
                "credito" => $cuenta->naturaleza_cuenta === PlanCuentas::CREDITO ? abs($detallePeriodoPago->valor) : 0,
            ]);

            $documentoGeneral->addRow($row);
        }

        // Verificar errores antes de guardar
        if (!empty($errors) || $documentoGeneral->hasErrors()) {
            $allErrors = array_merge($errors, $documentoGeneral->getErrors());
            return [
                'success' => false,
                'errors' => $allErrors
            ];
        }

        // Actualizar estado y guardar
        $periodoPago->estado = NomPeriodoPagos::ESTADO_CAUSADO;
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

    private function determineNitForConcepto($concepto, $contrato, $empleado)
    {
        $codigosConceptos = [
            500 => 'fondo_salud',
            501 => 'fondo_pension'
        ];

        if (isset($codigosConceptos[$concepto->codigo])) {
            $relacionAdministradoras = $codigosConceptos[$concepto->codigo];
            $administradora = $contrato->{$relacionAdministradoras};
            
            if (!$administradora->nit) {
                return [
                    'error' => [
                        'Tablas <b style="color: #000">→</b> Administradoras' => [
                            'id_nit' => "<b style='color: #481897'>{$administradora->codigo}</b> - <b style='color: #481897'>{$administradora->descripcion}</b> no tiene nit relacionado."
                        ]
                    ]
                ];
            }

            return ['id_nit' => $administradora->id_nit];
        }

        return ['id_nit' => $empleado->id];
    }
}