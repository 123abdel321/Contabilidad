<?php

namespace App\Helpers\Nomina\Calculator;

use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomPeriodos;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;
use Carbon\CarbonImmutable;

abstract class AbstractPeriodoPagoDetalle
{
	protected $contrato;

	/**
	 *
	*	@var NomPeriodoPagos
	*
	*/
	protected $periodoPago;
	private $diasMesBase = 30;

	public function __construct(NomContratos $contrato, NomPeriodoPagos $periodoPago)
	{
		$this->contrato = $contrato;
		$this->periodoPago = $periodoPago;
	}

	abstract function getInstance(): NomPeriodoPagoDetalles;

	/*
	*	Son los días que equivalen a un salario.
	*	ej:
	*	- si el salario es 1.000.000 por mes entonces
	* 	la base son 30 días.
	*
	*	- Si el salario es 1.000.000 por cada 2 meses entonces
	*	la base debe ser 60.
	*
	*/
	public function diasMesBase()
	{
		$diasCalendario = $this->contrato->periodo->periodo_dias_calendario;

		return $diasCalendario > $this->diasMesBase ? $diasCalendario : $this->diasMesBase;
	}

	public function diasTrabajados()
	{
		$fechaInicioPeriodo = CarbonImmutable::parse($this->periodoPago->fecha_inicio_periodo);
		$fechaFinPeriodo = CarbonImmutable::parse($this->periodoPago->fecha_fin_periodo);
		$fechaInicioContrato = CarbonImmutable::parse($this->contrato->fecha_inicio_contrato);
		$fechaFinContrato = $this->contrato->fecha_fin_contrato ? CarbonImmutable::parse($this->contrato->fecha_fin_contrato) : null;
		$fechaInicioReferencia = $fechaInicioPeriodo;
		$fechaFinReferencia = $fechaFinPeriodo;
		$lastDayOfMonth = $fechaFinReferencia->endOfMonth()->format('d');

		if ($fechaInicioContrato->greaterThan($fechaInicioPeriodo) && $fechaInicioContrato->lessThanOrEqualTo($fechaFinPeriodo)) {
			$fechaInicioReferencia = $fechaInicioContrato;
		}

		if ($fechaFinContrato && $fechaFinContrato->greaterThanOrEqualTo($fechaInicioPeriodo) && $fechaFinContrato->lessThan($fechaFinPeriodo)) {
			$fechaFinReferencia = $fechaFinContrato;
		}

		$diasTrabajados = $fechaFinReferencia->diffInDays($fechaInicioReferencia, true) + 1;

		if ($this->contrato->periodo->tipo_dia_pago === NomPeriodos::TIPO_DIA_PAGO_ORDINAL

		&& $diasTrabajados !== $this->contrato->periodo->dias_salario) {

			if ($this->contrato->periodo->dias_salario == 30 && $lastDayOfMonth == $diasTrabajados) {
				$diasTrabajados = $this->contrato->periodo->dias_salario;
			}

			if ($this->contrato->periodo->dias_salario == 15
			&& $lastDayOfMonth - $this->contrato->periodo->dias_salario == $diasTrabajados) {
				$diasTrabajados = $this->contrato->periodo->dias_salario;
			}
		}

		return $diasTrabajados;
	}

	public function horasTrabajadas()
	{
		return $this->diasTrabajados() * $this->contrato->periodo->horas_dia;
	}

	public function valorDiasTrabajados($base)
	{
		return $this->diasTrabajados() * $this->valorDia($base);
	}

	public function valorHorasTrabajadas($base)
	{
		return $this->horasTrabajadas() * $this->valorHora($base);
	}

	public function valorDia($base)
	{
		return $base / $this->diasMesBase();
	}

	public function valorHora($base)
	{
		return $this->valorDia($base) / $this->contrato->periodo->horas_dia;
	}
}
