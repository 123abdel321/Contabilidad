<?php

namespace App\Helpers\Nomina\Calculator;

use App\Models\Sistema\Nomina\NomConceptos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;

class PeriodoPagoDetalleTransporte extends AbstractPeriodoPagoDetalle
{
	private ?NomConceptos $conceptoTransporte = null;

	public function getInstance(): NomPeriodoPagoDetalles
	{
		$this->loadRequiredTransporteData();
		
		$concepto = $this->getTransporteConcepto();
		$valor = $this->calculateValorTransporte($concepto);

		return new NomPeriodoPagoDetalles([
			'id_concepto' => $concepto->id,
			'tipo_unidad' => $concepto->unidad,
			'unidades' => $this->calcularUnidades($concepto),
			'observacion' => $concepto->nombre,
			'valor' => $valor,
			'base' => $concepto->valor_mensual
		]);
	}

	private function loadRequiredTransporteData(): void
    {
        $this->contrato->loadMissing(['periodo']);
    }

	private function getTransporteConcepto(): NomConceptos
    {
        if ($this->conceptoTransporte === null) {
            $this->conceptoTransporte = NomConceptos::where('tipo_concepto', 'auxilio_transporte')
                ->firstOrFail();
                
            if ($this->conceptoTransporte->valor_mensual === null) {
                throw new RuntimeException("El concepto de transporte no tiene valor mensual configurado");
            }
        }
        
        return $this->conceptoTransporte;
    }

	private function calcularUnidades(NomConceptos $concepto): ?float
    {
        return match($concepto->unidad) {
            NomPeriodoPagoDetalles::TIPO_UNIDAD_HORAS => $this->horasTrabajadas(),
            NomPeriodoPagoDetalles::TIPO_UNIDAD_DIAS => $this->diasTrabajados(),
            default => null
        };
    }

	private function calculateValorTransporte(NomConceptos $concepto): float
    {
        $baseValue = $concepto->valor_mensual;
        
        if ($concepto->unidad === NomPeriodoPagoDetalles::TIPO_UNIDAD_HORAS) {
            return $this->valorHora($baseValue) * $this->calcularUnidades($concepto);
        }
        
        if ($concepto->unidad === NomPeriodoPagoDetalles::TIPO_UNIDAD_DIAS) {
            return $this->valorDia($baseValue) * $this->calcularUnidades($concepto);
        }
        
        return $baseValue;
    }
}
