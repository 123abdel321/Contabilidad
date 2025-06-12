<?php

namespace App\Helpers\Nomina;

use Carbon\CarbonImmutable;
use App\Models\Begranda\Tablas\Nomina\NomContratos;

class CalcularPeriodo
{
    /**
     * Calcula los periodos de calendario basados en el contrato
     * 
     * @param string $mes Mes a calcular en formato Y-m
     * @param NomContratos $contrato Modelo de contrato
     * @return array Array con fechas de inicio y fin de cada periodo
     */
    public function getPeriodosCalendario(string $mes, NomContratos $contrato): array
    {
        $periodo = $contrato->periodo;
        $mesCalcular = CarbonImmutable::parse($mes);
        $fechaInicioContrato = CarbonImmutable::parse($contrato->fecha_inicio_contrato);

        // $periodoPagoAnterior = $contrato->periodo_pago()
		// 	->select('fecha_inicio_periodo')
		// 	->whereDate('fecha_inicio_periodo', '<=', $mesCalcular->format('Y-m-d'))
		// 	->orderBy('fecha_inicio_periodo', 'DESC')
		// 	->first();

        // if ($periodoPagoAnterior) {
		// 	$fechaInicioContrato = CarbonImmutable::parse($periodoPagoAnterior->fecha_inicio_periodo);
		// }
        
        // Calculamos el número de periodos completos hasta el mes actual
        $diasTranscurridos = $mesCalcular->diffInDays($fechaInicioContrato);
        $periodosCompletos = (int) ($diasTranscurridos / $periodo->periodo_dias_calendario);
        $diasAjuste = $periodosCompletos * $periodo->periodo_dias_calendario;
        
        // Ajustamos la fecha de inicio
        $fechaInicioPeriodo = $fechaInicioContrato->addDays($diasAjuste);
        $diasPeriodo = $periodo->periodo_dias_calendario - 1;
        
        // Calculamos cuántos periodos caben en el mes
        $diasMes = $fechaInicioPeriodo->daysInMonth;
        $periodosEnMes = (int) round($diasMes / $periodo->periodo_dias_calendario, 0);
        
        $fechasPeriodos = [];
        
        for ($i = 0; $i < $periodosEnMes; $i++) {
            $fechaFinPeriodo = $fechaInicioPeriodo->addDays($diasPeriodo);
            
            $fechasPeriodos[] = [
                'fecha_inicio' => $fechaInicioPeriodo->toDateString(),
                'fecha_fin' => $fechaFinPeriodo->toDateString()
            ];
            
            $fechaInicioPeriodo = $fechaFinPeriodo->addDay();
        }
        
        return $fechasPeriodos;
    }

    /**
     * Calcula periodos ordinales basados en días específicos del mes
     * 
     * @param string $fecha Fecha de referencia en formato Y-m-d
     * @param array $periodosDiasOrdinales Array con días de corte para los periodos
     * @return array Array con fechas de inicio y fin de cada periodo
     */
    public function getPeriodosOrdinales(string $fecha, array $periodosDiasOrdinales): array
    {
        sort($periodosDiasOrdinales);
        $fechaReferencia = CarbonImmutable::parse($fecha);
        $ultimoDiaMes = $fechaReferencia->daysInMonth;
        $primerDiaPeriodo = 1;
        
        $periodos = [];
        
        foreach ($periodosDiasOrdinales as $diaFinPeriodo) {
            $diaFin = min($diaFinPeriodo, $ultimoDiaMes);
            
            $periodos[] = [
                'fecha_inicio' => $fechaReferencia->setDay($primerDiaPeriodo)->toDateString(),
                'fecha_fin' => $fechaReferencia->setDay($diaFin)->toDateString()
            ];
            
            $primerDiaPeriodo = $diaFin + 1;
        }
        
        return $periodos;
    }
}