<?php

namespace App\Helpers\Nomina\Calculator;

use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomNovedadesGenerales;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class PeriodoPagoDetalleNovedadGeneral extends AbstractPeriodoPagoDetalle
{
	private float $base = 0.0;
	private NomNovedadesGenerales $novedad;

	public function __construct(NomContratos $contrato, NomPeriodoPagos $periodoPago, NomNovedadesGenerales $novedad)
	{
		if (!$novedad->concepto) {
			throw new \InvalidArgumentException("La novedad debe tener un concepto asociado");
		}

		$this->novedad = $novedad;
		parent::__construct($contrato, $periodoPago);
	}

	public function getInstance(): NomPeriodoPagoDetalles
	{
		$this->loadRequiredNovedadData();

		$valor = $this->calculateValorNovedad();

		return new NomPeriodoPagoDetalles([
			'id_concepto' => $this->novedad->concepto->id,
			'tipo_unidad' => $this->novedad->concepto->unidad,
			'porcentaje' => $this->novedad->porcentaje ?: 0,
			'unidades' => $this->novedad->unidades,
			'observacion' => $this->novedad->observacion,
			'valor' => $valor,
			'base' => $this->base
		]);
	}

	private function loadRequiredNovedadData(): void
    {
        $this->contrato->loadMissing(['periodo', 'concepto_basico']);
	}

	public function setBase(float $base): self
    {
        $this->base = $base;
        return $this;
    }

	public function porcentaje(): float
    {
        return $this->novedad->porcentaje ?: $this->novedad->concepto->porcentaje ?: 100;
    }

	public function calculateValorNovedad(): float
    {
        $valor = $this->calculateBaseValue();
        $valor = $this->applyPercentage($valor);
        
        return round($valor, 2);
    }

	private function calculateBaseValue(): float
    {
        if ((float)$this->novedad->valor) {
            return (float)$this->novedad->valor;
        }

        $unidades = $this->novedad->unidades ?: 1;
        
        return match($this->novedad->concepto->unidad) {
            NomPeriodoPagoDetalles::TIPO_UNIDAD_HORAS => $this->valorHora($this->base) * $unidades,
            NomPeriodoPagoDetalles::TIPO_UNIDAD_DIAS => $this->valorDia($this->base) * $unidades,
            default => $this->novedad->valor ?: 0.0
        };
    }

	private function applyPercentage(float $value): float
    {
        return $value * ($this->porcentaje() / 100);
    }
}
