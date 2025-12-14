<?php

namespace App\Helpers\NominaElectronica;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomPeriodoPagos;

abstract class AbstractNESender
{
    protected $empleado;
	protected $periodosPagos;
	protected $nomElectronicaId;
	protected $nomElectronicaProcesada;
    protected $url = "https://fe.portafolioerp.com/api/ubl2.1";

    public function __construct($empleadoId, $periodosPagoIds, $nomElectronicaId, $nomProcesada = null)
    {
        $this->empleado = $this->loadEmpleado($empleadoId);
        $this->periodosPagos = $this->loadPeriodosPagos($periodosPagoIds);
        $this->nomElectronicaId = $nomElectronicaId;
        $this->nomElectronicaProcesada = $nomProcesada;
    }

    public abstract function getExtraParams(): array;
	public abstract function getEndpoint(): string;

    function getConfigApiNE ()
    {
		$entorno = VariablesEntorno::whereIn('nombre', ['token_key_fe', 'set_test_id_fe', 'software_provider_id'])->get();

		$softwareProviderId = '';
		$bearerToken = '';
		$setTestId = '';

		if (count($entorno)) {
			$bearerToken = $entorno->firstWhere('nombre', 'token_key_fe');
			$bearerToken = $bearerToken ? $bearerToken->valor : '';

			$setTestId = $entorno->firstWhere('nombre', 'set_test_id_fe');
			$setTestId = $setTestId && $setTestId->valor ? '/' . $setTestId->valor	: '';

			$softwareProviderId = $entorno->firstWhere('nombre', 'software_provider_id');
			$softwareProviderId = $softwareProviderId ? $softwareProviderId->valor : '';
		}

		$this->softwareProviderId =  $softwareProviderId;

		return [$bearerToken, $setTestId];
	}

    public function getUrl()
	{
		$enviroment = env('APP_ENV') == 'local' ? '' : '';

		return $this->url . $this->getEndpoint() . $enviroment;
	}

    public function send () 
    {
        [$bearerToken, $setTestId] = $this->getConfigApiNE();
        $params = $this->getParams();

        dd($params);
    }

    public function getParams(): array
	{
		return array_merge($this->getExtraParams(), array(
			'period' => $this->period(),
			'worker_code' => $this->empleado->numero_documento,
			'consecutive' => $this->nomElectronicaId,
			'payroll_period_id' => '4',
			'worker' => $this->worker(),
            'payment' => $this->payment(),
			'payment_dates' => $this->paymentDates(),
			'accrued' => $this->accrued(),
			'deductions' => $this->deductions()
		));
	}

    public function period(): array
    {
        // Validación básica
        if ($this->periodosPagos->isEmpty()) {
            throw new \Exception('No hay periodos de pago para procesar');
        }
        
        if (!$this->empleado->contrato_actual) {
            throw new \Exception('El empleado no tiene contrato activo');
        }
        
        // 1. Inicializar con el primer periodo
        $firstPeriod = $this->periodosPagos->first();
        $startDate = Carbon::parse($firstPeriod->fecha_inicio_periodo);
        $endDate = Carbon::parse($firstPeriod->fecha_fin_periodo);
        
        // 2. Encontrar el rango completo de periodos
        foreach ($this->periodosPagos as $periodoPago) {
            $periodStart = Carbon::parse($periodoPago->fecha_inicio_periodo);
            $periodEnd = Carbon::parse($periodoPago->fecha_fin_periodo);
            
            if ($periodStart->lt($startDate)) $startDate = $periodStart;
            if ($periodEnd->gt($endDate)) $endDate = $periodEnd;
        }
        
        // 3. Ajustar con fechas del contrato
        $contrato = $this->empleado->contrato_actual;
        $contratoStart = Carbon::parse($contrato->fecha_inicio_contrato);
        $contratoEnd = $contrato->fecha_fin_contrato 
            ? Carbon::parse($contrato->fecha_fin_contrato) 
            : null;
        
        if ($contratoStart->gt($startDate)) $startDate = $contratoStart;
        if ($contratoEnd && $contratoEnd->lt($endDate)) $endDate = $contratoEnd;
        
        // 4. Retornar respuesta
        return [
            'admision_date' => $contratoStart->format('Y-m-d'),
            'settlement_start_date' => $startDate->format('Y-m-d'),
            'settlement_end_date' => $endDate->format('Y-m-d'),
            'worked_time' => $startDate->diffInDays($endDate) + 1,
            'issue_date' => Carbon::now()->format('Y-m-d')
        ];
    }

    public function worker(): array
	{
		return [
			'type_worker_id' => 1, //01 Dependiente
			'sub_type_worker_id' => 0, //00 No aplica
			'payroll_type_document_identification_id' => $this->empleado->id_tipo_documento,
			'municipality_id' => $this->empleado->ciudad->codigo ?? '05001',
			'type_contract_id' => $this->empleado->contrato_actual->termino,
			'high_risk_pension' => false,
			'identification_number' => $this->empleado->numero_documento,
			'surname' => $this->empleado->primer_apellido ?? '',
			'second_surname' => $this->empleado->segundo_apellido ?? '',
			'first_name' => $this->empleado->primer_nombre ?? '',
			'middle_name' => $this->empleado->otros_nombres ?? '',
			'address' =>  $this->empleado->direccion ?? '',
			'integral_salarary' => $this->empleado->contrato_actual->tipo_salario == NomContratos::TIPO_SALARIO_INTEGRAL ?? false,
			'salary' => floatval(number_format($this->empleado->contrato_actual->salario ?? 0, 2, '.', ''))
            
		];
	}

    public function payment(): array
    {
        return [
            'payment_method_id' => '10',
            'bank_name' => $this->empleado->banco ? $this->empleado->banco->nombre : '',
            'account_type' => $this->empleado->tipo_cuenta_banco ? 'Ahorro' : 'Corriente',
            'account_number' => $this->empleado->cuenta_bancaria
        ];
    }

    public function paymentDates()
	{
        return $this->periodosPagos
            ->map(function($periodoPago) {
                $paymentDate = $periodoPago->fecha_pago ?? $periodoPago->updated_at;
                
                return [
                    'payment_date' => Carbon::parse($paymentDate, 'UTC')->format('Y-m-d')
                ];
            })
            ->unique('payment_date') // Eliminar duplicados
            ->values()
            ->toArray();
    }

    public function accrued(): array
    {
        // Validar
        if (!$this->empleado->contrato_actual) {
            throw new \Exception('Contrato no encontrado');
        }

        // Inicializar
        $accrued = $this->initAccrued();
        $formatters = $this->getAccruedFormatters();
        
        // Procesar
        foreach ($this->periodosPagos as $periodo) {
            $accrued['accrued_total'] += floatval($periodo->sumDetalles->devengados ?? 0);
            
            foreach ($periodo->detalles as $detalle) {
                $codigo = $detalle->concepto->codigo ?? '';
                $this->addAccruedItem($detalle, $codigo, $formatters, $accrued);
            }
        }

        // Retornar sin arreglos vacios
        return array_filter($accrued, function($value) {
            if (is_array($value)) {
                return !empty($value);
            }
            return true;
        });
    }

    private function deductions(): array
    {
        // Inicializar
        $deductions = $this->initDeductions();
        $processors = $this->getDeductionProcessors();

        foreach ($this->periodosPagos as $periodo) {
            $this->processDeductionPeriod($periodo, $processors, $deductions);
        }

        return $deductions;
    }

    private function initDeductions(): array
    {
        $base = [
            "deductions_total" => 0,
            "eps_deduction" => 0,
            "eps_type_law_deductions_id" => 1,
            "pension_deduction" => 0,
            "pension_type_law_deductions_id" => 5,
            "voluntary_pension" => 0,
            "afc" => 0
        ];

        $types = ['labor_union', 'sanctions', 'orders', 'third_party_payments', 
                'advances', 'other_deductions', 'unmapped'];
        
        foreach ($types as $type) {
            $base[$type] = 0;
        }

        return $base;
    }

    private function getDeductionProcessors(): array
    {
        return [
            '500' => fn($d, &$ded) => $ded['eps_deduction'] += abs(floatval($d->valor)),
            '501' => fn($d, &$ded) => $ded['pension_deduction'] += abs(floatval($d->valor)),
            '504' => fn($d, &$ded) => $ded['voluntary_pension'] += abs(floatval($d->valor)),
            '505' => fn($d, &$ded) => $ded['afc'] += abs(floatval($d->valor)),
            '510' => fn($d) => $this->processAdvance($d),
            '520' => fn($d) => $this->processOtherDeduction($d),
        ];
    }

    private function processDeductionPeriod($periodo, array $processors, array &$deductions): void
    {
        $deductions['deductions_total'] += abs(floatval($periodo->sumDetalles->deducciones ?? 0));

        foreach ($periodo->detalles as $detalle) {
            $valor = floatval($detalle->valor ?? 0);
            
            // Solo procesar si es deducción (valor negativo)
            if ($valor >= 0) continue;

            $codigo = $detalle->concepto->codigo ?? '';
            $this->applyDeductionProcessor($detalle, $codigo, $processors, $deductions);
        }
    }

    private function applyDeductionProcessor($detalle, string $codigo, array $processors, array &$deductions): void
    {
        if (isset($processors[$codigo])) {
            $processors[$codigo]($detalle, $deductions);
        } else {
            $this->addToUnmapped($detalle, $deductions);
        }
    }

    private function processAdvance($detalle): void
    {
        
    }

    private function processOtherDeduction($detalle): void
    {

    }

    private function addToUnmapped($detalle, array &$deductions): void
    {
        $deductions['unmapped'][] = [
            'code' => $detalle->concepto->codigo ?? '000',
            'name' => $detalle->concepto->nombre ?? 'Desconocido',
            'value' => abs(floatval($detalle->valor))
        ];
    }

    private function loadEmpleado($empleadoId)
    {
        return Nits::where('id', $empleadoId)
            ->with(['contrato_actual.periodo', 'electronica', 'ciudad'])
            ->firstOrFail();
    }
    
    private function loadPeriodosPagos($periodosPagoIds)
    {
        return NomPeriodoPagos::whereIn('id', $periodosPagoIds)
            ->with(['detalles.concepto', 'sumDetalles'])
            ->get();
    }

    private function initAccrued(): array
    {
        $accrued = [
            "worked_days" => $this->diasTrabajados(),
            "salary" => floatval($this->empleado->contrato_actual->salario),
            "accrued_total" => 0,
            "transportation_allowance" => 0
        ];

        // Tipos predefinidos
        $types = ['HEDs', 'HENs', 'HRNs', 'HEDDFs', 'HRDDFs', 'HENDFs', 'HRNDFs',
                'common_vacation', 'paid_vacation', 'service_bonus', 'work_disabilities',
                'maternity_leave', 'paid_leave', 'non_paid_leave', 'commissions', 
                'aid', 'other_concepts'];
        
        foreach ($types as $type) {
            $accrued[$type] = [];
        }

        return $accrued;
    }

    private function diasTrabajados(): int
    {
        if (empty($this->periodosPagos)) {
            return 0;
        }

        // Extraemos todas las fechas de inicio y fin
        $fechasInicio = collect($this->periodosPagos)->map(fn($p) => Carbon::parse($p->fecha_inicio_periodo));
        $fechasFin = collect($this->periodosPagos)->map(fn($p) => Carbon::parse($p->fecha_fin_periodo));

        // Obtenemos los extremos
        $minFecha = $fechasInicio->min();
        $maxFecha = $fechasFin->max();

        // diffInDays + 1 para incluir el día inicial
        return (int) $minFecha->diffInDays($maxFecha) + 1;
    }

    private function getAccruedFormatters(): array
    {
        return [//$d => Detalle; $a => $accrued
            '015' => fn($d, &$a) => $a['transportation_allowance'] += floatval($d->valor ?? 0),
            '006' => fn($d) => ['type' => 'HEDs', 'data' => $this->formatHourly($d)],
            '007' => fn($d) => ['type' => 'HENs', 'data' => $this->formatHourly($d)],
            '003' => fn($d) => ['type' => 'HRNs', 'data' => $this->formatHourly($d)],
            '008' => fn($d) => ['type' => 'HEDDFs', 'data' => $this->formatHourly($d)],
            '004' => fn($d) => ['type' => 'HRDDFs', 'data' => $this->formatHourly($d)],
            '005' => fn($d) => ['type' => 'HRNDFs', 'data' => $this->formatHourly($d)],
            '009' => fn($d) => ['type' => 'HENDFs', 'data' => $this->formatHourly($d)],
            '033' => fn($d) => ['type' => 'common_vacation', 'data' => $this->formatDateRange($d)],
            '034' => fn($d) => ['type' => 'paid_vacation', 'data' => $this->formatSimple($d)],
            '041' => fn($d) => ['type' => 'service_bonus', 'data' => $this->formatSimple($d)],
            '042' => fn($d) => ['type' => 'service_bonus', 'data' => $this->formatSimple($d)],
            '027' => fn($d) => ['type' => 'work_disabilities', 'data' => $this->formatWithType($d)],
            '028' => fn($d) => ['type' => 'maternity_leave', 'data' => $this->formatDateRange($d)],
            '022' => fn($d) => ['type' => 'paid_leave', 'data' => $this->formatDateRange($d)],
            '023' => fn($d) => ['type' => 'non_paid_leave', 'data' => $this->formatDateRange($d)],
            '040' => fn($d) => ['type' => 'commissions', 'data' => $this->formatCommission($d)],
            '043' => fn($d) => ['type' => 'aid', 'data' => $this->formatSimple($d)],
            '044' => fn($d) => ['type' => 'aid', 'data' => $this->formatSimple($d)],
        ];
    }

    private function addAccruedItem($detalle, string $codigo, array $formatters, array &$accrued): void
    {
        if (!isset($formatters[$codigo])) {
            // $accrued['other_concepts'][] = $this->formatGeneric($detalle);
            return;
        }

        $result = $formatters[$codigo]($detalle, $accrued);
        
        if (is_array($result)) {
            $accrued[$result['type']][] = $result['data'];
        }
    }

    /**
     * Formato para conceptos por horas (HEDs, HENs, HRNs, etc.)
     */
    private function formatHourly($detalle): array
    {
        return [
            'start_time' => $this->formatDateTime($detalle->fecha_inicio ?? null, $detalle->hora_inicio ?? null),
            'end_time' => $this->formatDateTime($detalle->fecha_fin ?? null, $detalle->hora_fin ?? null),
            'quantity' => floatval($detalle->unidades ?? 0),
            'percentage' => floatval($detalle->porcentaje ?? 0),
            'payment' => floatval($detalle->valor ?? 0)
        ];
    }

    /**
     * Formato para conceptos con rango de fechas (vacaciones, incapacidades, licencias)
     */
    private function formatDateRange($detalle): array
    {
        return [
            'start_date' => $detalle->fecha_inicio ?? '',
            'end_date' => $detalle->fecha_fin ?? '',
            'quantity' => floatval($detalle->unidades ?? 0),
            'payment' => floatval($detalle->valor ?? 0)
        ];
    }

    /**
     * Formato para conceptos con tipo específico (incapacidades con tipo)
     */
    private function formatWithType($detalle): array
    {
        return [
            'start_date' => $detalle->fecha_inicio ?? '',
            'end_date' => $detalle->fecha_fin ?? '',
            'quantity' => floatval($detalle->unidades ?? 0),
            'type' => $detalle->tipo_incapacidad ?? '', // Asumiendo que existe este campo
            'payment' => floatval($detalle->valor ?? 0)
        ];
    }

    /**
     * Formato para conceptos simples (bonos, auxilios, etc.)
     */
    private function formatSimple($detalle): array
    {
        return [
            'quantity' => floatval($detalle->unidades ?? 0),
            'payment' => floatval($detalle->valor ?? 0)
        ];
    }

    /**
     * Formato específico para comisiones
     */
    private function formatCommission($detalle): array
    {
        return [
            'commissions' => floatval($detalle->valor ?? 0)
        ];
    }

    /**
     * Formato genérico para conceptos no configurados
     */
    private function formatGeneric($detalle): array
    {
        return [
            'concept_code' => $detalle->concepto->codigo ?? '000',
            'concept_name' => $detalle->concepto->nombre ?? 'Desconocido',
            'quantity' => floatval($detalle->unidades ?? 0),
            'payment' => floatval($detalle->valor ?? 0),
            'start_date' => $detalle->fecha_inicio ?? null,
            'end_date' => $detalle->fecha_fin ?? null,
            'percentage' => floatval($detalle->porcentaje ?? 0)
        ];
    }

    /**
     * Helper para formatear fecha y hora combinadas
     */
    private function formatDateTime(?string $date, ?string $time): string
    {
        if (!$date || !$time) {
            return '';
        }
        
        try {
            return Carbon::parse($date . ' ' . $time, 'UTC')
                ->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            return '';
        }
    }

}