<?php

namespace App\Helpers\Nomina\Calculator;

use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class PeriodoPagoDetalleSalarioBase extends AbstractPeriodoPagoDetalle
{
	public function getInstance(): NomPeriodoPagoDetalles
	{
		$this->loadRequiredSalarioData();

		$concepto = $this->contrato->concepto_basico;
        $base = $this->contrato->salario;

		$unidades = $this->calcularUnidades($concepto);
		$valor = $this->calculateValorSalarioBase($concepto, $base);

		return new NomPeriodoPagoDetalles([
            'id_concepto' => $this->contrato->id_concepto_basico,
            'tipo_unidad' => $concepto->unidad,
            'unidades' => $unidades,
            'valor' => $valor,
            'observacion' => $concepto->nombre,
        ]);
	}

	private function loadRequiredSalarioData(): void
    {
        $this->contrato->loadMissing(['periodo', 'concepto_basico']);
        
        if (!$this->contrato->concepto_basico) {
            throw new RuntimeException("El contrato no tiene concepto básico asociado");
        }
    }

	private function calcularUnidades(NomConceptos $concepto): ?float
    {
        return match($concepto->unidad) {
            NomPeriodoPagoDetalles::TIPO_UNIDAD_HORAS => $this->horasTrabajadas(),
            NomPeriodoPagoDetalles::TIPO_UNIDAD_DIAS => $this->diasTrabajados(),
            default => null
        };
    }

	private function calculateValorSalarioBase(NomConceptos $concepto, float $base): float
    {
        if ($concepto->valor_mensual !== null) {
            return (float)$concepto->valor_mensual;
        }

        $unidades = $this->calcularUnidades($concepto) ?? 1;

        return match($concepto->unidad) {
            NomPeriodoPagoDetalles::TIPO_UNIDAD_HORAS => $this->valorHora($base) * $unidades,
            NomPeriodoPagoDetalles::TIPO_UNIDAD_DIAS => $this->valorDia($base) * $unidades,
            default => $base
        };
    }
}
