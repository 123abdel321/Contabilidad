<?php

namespace App\Helpers\Nomina\Calculator;

//MODELS
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomNovedadesGenerales;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class PeriodoPagoDetalleFactory
{
	public function createPeriodoPagoDetalleSalarioBase(NomContratos $contrato, NomPeriodoPagos $periodoPago)
	{
		return (new PeriodoPagoDetalleSalarioBase($contrato, $periodoPago))
			->getInstance();
	}

	public function createPeriodoPagoDetalleTransporte(NomContratos $contrato, NomPeriodoPagos $periodoPago): NomPeriodoPagoDetalles
	{
		return (new PeriodoPagoDetalleTransporte($contrato, $periodoPago))
			->getInstance();
	}

	public function createPeriodoPagoDetalleNovedadGeneral(NomContratos $contrato, NomPeriodoPagos $periodoPago, NomNovedadesGenerales $novedad, array $periodoPagoDetalles)
	{
		$novedad->loadMissing('concepto');

		$base = $contrato->salario;
		$base = (float) $novedad->concepto->valor_mensual ? $novedad->concepto->valor_mensual : $base;
		$base = (float) $novedad->valor ? $novedad->valor : $base;

		if ($novedad->concepto->id_concepto_porcentaje) {
			$base = array_reduce($periodoPagoDetalles, function ($base, $detalle) use ($novedad) {
				if ($detalle->id_concepto == $novedad->concepto->id_concepto_porcentaje) {
					$base = $base + $detalle->valor;
				}

				return $base;
			}, 0);
		}

		return (new PeriodoPagoDetalleNovedadGeneral($contrato, $periodoPago, $novedad))
			->setBase($base)
			->getInstance();
	}

	public function createPeriodoPagoDetalleSalud(NomContratos $contrato, NomPeriodoPagos $periodoPago, array $periodoPagoDetalles): NomPeriodoPagoDetalles
	{
		$base = array_reduce($periodoPagoDetalles, function ($base, $detalle) {
			$detalle->loadMissing('concepto');

			if (!$detalle->concepto->base_salud) return $base;

			return $base + $detalle->valor;
		}, 0);

		return (new PeriodoPagoDetalleSalud($contrato, $periodoPago))
			->setBase($base)
			->getInstance();
	}

	public function createPeriodoPagoDetallePension(NomContratos $contrato, NomPeriodoPagos $periodoPago, array $periodoPagoDetalles): NomPeriodoPagoDetalles
	{
		$base = array_reduce($periodoPagoDetalles, function ($base, $detalle) {
			$detalle->loadMissing('concepto');

			if (!$detalle->concepto->base_pension) return $base;

			return $base + $detalle->valor;
		}, 0);

		return (new PeriodoPagoDetallePension($contrato, $periodoPago))
			->setBase($base)
			->getInstance();
	}
}
