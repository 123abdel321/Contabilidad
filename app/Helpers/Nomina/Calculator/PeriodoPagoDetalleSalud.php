<?php

namespace App\Helpers\Nomina\Calculator;

use InvalidArgumentException;
use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class PeriodoPagoDetalleSalud extends AbstractPeriodoPagoDetalle
{
	private float $base = 0.0;
	private ?NomConceptos $conceptoSalud = null;

	public function getInstance(): NomPeriodoPagoDetalles
	{
		$this->loadRequiredSaludData();

		$concepto = $this->getSaludConcepto();
		$valor = $this->calcularValorSalud($concepto);

		return new NomPeriodoPagoDetalles([
			'id_concepto' => $concepto->id,
			'tipo_unidad' => $concepto->unidad,
			'unidades' => null,
			'observacion' => $concepto->nombre,
			'valor' => $valor,
			'base' => $this->base
		]);
	}

	private function loadRequiredSaludData(): void
	{
		$this->contrato->loadMissing(['periodo']);
        
        if (!isset($this->contrato->tipo_salario)) {
            throw new InvalidArgumentException("El contrato no tiene definido el tipo de salario");
        }
	}

	private function getSaludConcepto(): NomConceptos
    {
        if ($this->conceptoSalud === null) {
            $this->conceptoSalud = NomConceptos::where('tipo_concepto', 'salud')
                ->firstOrFail();
                
            if (!$this->conceptoSalud->porcentaje) {
                throw new InvalidArgumentException("El concepto de salud no tiene porcentaje configurado");
            }
        }
        
        return $this->conceptoSalud;
    }

	private function calcularValorSalud(NomConceptos $concepto): float
    {
        $salarioPercentaje = $this->getSalarioPercentaje();
        $saludPercentaje = $concepto->porcentaje / 100;
        
        $value = $this->base * $salarioPercentaje * $saludPercentaje;
        
        return round($value * -1, 2);
    }

	private function getSalarioPercentaje(): float
    {
        return $this->contrato->tipo_salario == NomContratos::TIPO_SALARIO_INTEGRAL 
            ? 0.7  // 70%
            : 1.0; // 100%
    }

	public function setBase(float $base): self
	{
		$this->base = $base;
		return $this;
	}
}
