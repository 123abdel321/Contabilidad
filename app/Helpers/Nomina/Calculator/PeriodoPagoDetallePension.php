<?php

namespace App\Helpers\Nomina\Calculator;

use InvalidArgumentException;
use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class PeriodoPagoDetallePension extends AbstractPeriodoPagoDetalle
{
	private float $base = 0.0;
	private ?NomConceptos $conceptoPension = null;

	public function getInstance(): NomPeriodoPagoDetalles
	{
		$this->loadRequiredPensionData();
		
		$concepto = $this->getPensionConcepto();
		$valor = $this->calculateValorPension($concepto);

		return new NomPeriodoPagoDetalles([
			'id_concepto' => $concepto->id,
			'tipo_unidad' => $concepto->unidad,
			'observacion' => $concepto->nombre,
			'unidades' => null,
			'valor' => $valor,
			'base' => $this->base
		]);
	}

	private function loadRequiredPensionData(): void
    {
        $this->contrato->loadMissing(['periodo']);

        if (!isset($this->contrato->tipo_salario)) {
            throw new InvalidArgumentException("El contrato no tiene definido el tipo de salario");
        }
	}

	private function getPensionConcepto(): NomConceptos
    {
        if ($this->conceptoPension === null) {
            $this->conceptoPension = NomConceptos::where('tipo_concepto', 'fondo_pension')
                ->firstOrFail();
                
            if (!$this->conceptoPension->porcentaje) {
                throw new InvalidArgumentException("El concepto de pensión no tiene porcentaje configurado");
            }
        }
        
        return $this->conceptoPension;
    }

	private function calculateValorPension(NomConceptos $concepto): float
    {
        $salaryPercentaje = $this->getSalarioPercentaje();
        $pensionPercentaje = $concepto->porcentaje / 100;
        
        $value = $this->base * $salaryPercentaje * $pensionPercentaje;
        
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
