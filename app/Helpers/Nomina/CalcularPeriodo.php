<?php

namespace App\Helpers\Nomina;

use Carbon\CarbonImmutable;
use App\Helpers\Nomina\Calculator\PeriodoPagoDetalleFactory;
//MODELS
use App\Models\Sistema\Nomina\NomPeriodos;
use App\Models\Sistema\Nomina\NomContratos;
use App\Models\Sistema\Nomina\NomPeriodoPagos;

class CalcularPeriodo
{
    protected $periodoPagoDetalleFactory;

    public function __construct()
	{
        $this->periodoPagoDetalleFactory = new PeriodoPagoDetalleFactory();
	}

    public function calcularNominas(string $fechaPeriodo = null, array $idsEmpleados = null, array $idsPeriodos = null)
    {
        $periodoPagos = [];
        
        try {

            $query = NomContratos::with(['periodo', 'concepto_basico'])
                ->where('estado', NomContratos::ESTADO_ACTIVO);

            // Filtro por fecha
            if (!empty($fechaPeriodo)) {
                $query->where(function($q) use ($fechaPeriodo) {
                    $q->where('fecha_inicio_contrato', 'like', $fechaPeriodo . '%')
						->orWhere('fecha_inicio_contrato', '<=', $fechaPeriodo);
                });
            }

            // Filtro por empleados (acepta array o valor único)
            if (!empty($idsEmpleados)) {
                $query->whereIn('id_empleado', $idsEmpleados);
            }
            
            // Filtro por periodos
            if (!empty($idsPeriodos)) {
                $query->whereHas('periodo_pago', function($q) use ($idsPeriodos) {
                    $q->whereIn('id', $idsPeriodos);
                });
            }

            // Procesar cada contrato
            $query->chunk(200, function($contratos) use (&$periodoPagos, $fechaPeriodo) {

                foreach ($contratos as $contrato) {

                    $periodo = $contrato->periodo;
                    $fechasPeriodos = $this->getPeriodoPago($contrato, $fechaPeriodo);

                    foreach ($fechasPeriodos as $fechasPeriodo) {
                        $inicioPeriodo = CarbonImmutable::parse($fechasPeriodo['fecha_inicio']);
						$finPeriodo = CarbonImmutable::parse($fechasPeriodo['fecha_fin']);
						$inicioContrato = CarbonImmutable::parse($contrato->fecha_inicio_contrato);
						$finContrato = $contrato->fecha_fin_contrato ? CarbonImmutable::parse($contrato->fecha_fin_contrato) : null;

                        $isInicioContrato = $inicioContrato->isBetween($inicioPeriodo, $finPeriodo, true);
						$isFinContrato = $finContrato && $finContrato->isBetween($inicioPeriodo, $finPeriodo, true);

                        $isContratoVigente = $isInicioContrato || $isFinContrato || ($contrato->fecha_inicio_contrato <= $fechasPeriodo['fecha_inicio']
							&& ($contrato->fecha_fin_contrato >= $fechasPeriodo['fecha_fin'] || $contrato->fecha_fin_contrato == null));

                        if (!$isContratoVigente) {
							continue;
						}

                        $periodoPago = NomPeriodoPagos::firstOrCreate(
							[
								"id_empleado" => $contrato->id_empleado,
								"id_contrato" => $contrato->id,
								"fecha_inicio_periodo" => $fechasPeriodo['fecha_inicio'],
								"fecha_fin_periodo" => $fechasPeriodo['fecha_fin'],
							],
							[
								"estado" => NomPeriodoPagos::ESTADO_PENDIENTE,
							]
						);

                        if ($periodoPago->estado !== NomPeriodoPagos::ESTADO_PENDIENTE) continue;

                        $periodoPago->novedades()->update(['id_periodo_pago' => $periodoPago->id]);
						$periodoPago->detalles()->delete();
                        
                        $periodoPagos[] = $this->createPeriodoPagoDetalles($contrato, $periodoPago);
                    }
                }
            });

            return $periodoPagos;

        } catch (Exception $exception) {
			DB::connection('sam')->rollback();
			throw new Exception('Error al calcular periodo.');
		}
    }

    private function createPeriodoPagoDetalles(NomContratos $contrato, NomPeriodoPagos $periodoPago)
    {
        $periodoPagoDetalles = [];
        $periodoPagoDetalles[] = $this->periodoPagoDetalleFactory->createPeriodoPagoDetalleSalarioBase($contrato, $periodoPago);

        if ($contrato->auxilio_transporte) {
			$periodoPagoDetalles[] = $this->periodoPagoDetalleFactory->createPeriodoPagoDetalleTransporte($contrato, $periodoPago);
		}

        $periodoPago->loadMissing(['novedades.concepto']);
		$novedades = $periodoPago->novedades->sortBy(function($novedad, $key) {
			return $novedad->concepto->id_concepto_porcentaje;
		});
        
        foreach ($novedades as $novedad) {
            if(count(explode("liquidacion/:",$novedad->observacion)) > 1) {
				$periodoPagoDetalles[] = $this->periodoPagoDetalleFactory->createPeriodoPagoDetalleNovedadGeneralLiquidacion($novedad);
			} else {
				$periodoPagoDetalles[] = $this->periodoPagoDetalleFactory->createPeriodoPagoDetalleNovedadGeneral($contrato, $periodoPago, $novedad, $periodoPagoDetalles);
			}
        }

        $periodoPagoDetalles[] = $this->periodoPagoDetalleFactory->createPeriodoPagoDetalleSalud($contrato, $periodoPago, $periodoPagoDetalles);
        $periodoPagoDetalles[] = $this->periodoPagoDetalleFactory->createPeriodoPagoDetallePension($contrato, $periodoPago, $periodoPagoDetalles);

        $periodoPago->detalles()->saveMany($periodoPagoDetalles);
        
        return $periodoPagoDetalles;
    }

    public function getPeriodosCalendario(string $mes, NomContratos $contrato): array
    {
        $periodo = $contrato->periodo;
        $mesCalcular = CarbonImmutable::parse($mes);
        $fechaInicioContrato = CarbonImmutable::parse($contrato->fecha_inicio_contrato);

        $periodoPagoAnterior = $contrato->periodo_pago()
			->select('fecha_inicio_periodo')
			->whereDate('fecha_inicio_periodo', '<=', $mesCalcular->format('Y-m-d'))
			->orderBy('fecha_inicio_periodo', 'DESC')
			->first();

        if ($periodoPagoAnterior) {
			$fechaInicioContrato = CarbonImmutable::parse($periodoPagoAnterior->fecha_inicio_periodo);
		}
        
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

    protected function getPeriodoPago ($contrato, $fecha)
    {
        $periodo = $contrato->periodo;

        if ($periodo->tipo_dia_pago == NomPeriodos::TIPO_DIA_PAGO_ORDINAL) {
            $periodoDiasOrdinales = str_replace(' ', '', $periodo->periodo_dias_ordinales);
            $periodoDiasOrdinales = explode(',', $periodo->periodo_dias_ordinales);
            $fechasPeriodos = $this->getPeriodosOrdinales($fecha, $periodoDiasOrdinales);
        }

        if ($periodo->tipo_dia_pago == NomPeriodos::TIPO_DIA_PAGO_CALENDARIO) {
            $fechasPeriodos = $this->getPeriodosCalendario($fecha, $contrato);
        }

        return $fechasPeriodos;
    }
}