<?php

namespace App\Http\Controllers\Traits;

use DB;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\DocumentosGeneral;


trait BegConsecutiveTrait
{
    /**
     * @param mixed $model
     * @param Comprobantes|int $comprobante
     * @param string $fecha
     *
     * @return int|null
     */
    public function getNextConsecutive($comprobante, string $fecha)
    {
		if (is_numeric($comprobante) > 0) {
			$comprobante = Comprobantes::find($comprobante);
		}

		if (!($comprobante instanceof Comprobantes)) {
			return null;
        }

        if (!$comprobante) {
			return null;
        }

        if ($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
            return  $this->getLastConsecutive($comprobante->id, $fecha) + 1;
        }

		return $comprobante->consecutivo_siguiente;
    }

    public function getNextConsecutiveBodega($bodega)
    {
        if (is_numeric($bodega) > 0) {
			$bodega = FacBodegas::find($bodega);
		}

        if (!($bodega instanceof FacBodegas)) {
			return null;
        }

        if (!$bodega) {
			return null;
        }

        return $bodega->consecutivo;
    }

    public function getNextConsecutiveBodegaParqueadero($bodega)
    {
        if (is_numeric($bodega) > 0) {
			$bodega = FacBodegas::find($bodega);
		}

        if (!($bodega instanceof FacBodegas)) {
			return null;
        }

        if (!$bodega) {
			return null;
        }

        return $bodega->consecutivo_parqueadero;
    }

	static function getLastConsecutive($id_comprobante, $fecha)
	{
		$castConsecutivo = 'MAX(CAST(consecutivo AS SIGNED)) AS consecutivo';
		$lastConsecutivo = DocumentosGeneral::select(DB::raw($castConsecutivo))
			->where('id_comprobante', $id_comprobante)
			->where('fecha_manual', 'like', substr($fecha, 0, 7) . '%')
			->first();

		return $lastConsecutivo ? $lastConsecutivo->consecutivo : 0;
	}

    /**
     * @param Comprobantes|int $comprobante
     * @param int $consecutivoActual
     *
     * @return Comprobantes|bool
     */
    public function updateConsecutivo($comprobante, int $consecutivoActual)
    {
        if (is_numeric($comprobante)) {
            $comprobante = Comprobantes::find($comprobante);
        } else if (!($comprobante instanceof Comprobantes)) {
            return false;
        }

		if($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
			$comprobante->consecutivo_siguiente = $consecutivoActual;
		}

		if ($consecutivoActual > $comprobante->consecutivo_siguiente) {
			$comprobante->consecutivo_siguiente = $consecutivoActual;
		}

		$comprobante->consecutivo_siguiente = $comprobante->consecutivo_siguiente + 1;
		$comprobante::unsetEventDispatcher();
		$comprobante->save();

        $comprobante->resolucion()->update(["consecutivo" => $comprobante->consecutivo_siguiente]);

        return $comprobante;
    }

    public function updateConsecutivoBodega($bodega, int $consecutivoActual)
    {
        if (is_numeric($bodega)) {
            $bodega = FacBodegas::find($bodega);
        } else if (!($bodega instanceof bodega)) {
            return false;
        }
        
		if ($consecutivoActual > $bodega->consecutivo) {
			$bodega->consecutivo = $consecutivoActual;
		}

		$bodega->consecutivo = $bodega->consecutivo + 1;
		$bodega::unsetEventDispatcher();
		$bodega->save();

        return $bodega;
    }

    public function updateConsecutivoParqueadero($bodega, int $consecutivoActual)
    {
        if (is_numeric($bodega)) {
            $bodega = FacBodegas::find($bodega);
        } else if (!($bodega instanceof bodega)) {
            return false;
        }
        
		if ($consecutivoActual > $bodega->consecutivo_parqueadero) {
			$bodega->consecutivo_parqueadero = $consecutivoActual;
		}

		$bodega->consecutivo_parqueadero = $bodega->consecutivo_parqueadero + 1;
		$bodega::unsetEventDispatcher();
		$bodega->save();

        return $bodega;
    }

    public function consecutivoUsado(Comprobantes $comprobante, int $consecutivo, $fecha_manual, $captura = null)
    {
        $consecutivoUsado = DocumentosGeneral::where('id_comprobante', $comprobante->id)
            ->where('consecutivo', $consecutivo);

        if ($captura) {
            $consecutivoUsado->whereNot('relation_id', $captura->id)
                ->whereNot('relation_type', $captura->getMorphClass());
        }

        if ($comprobante->tipo_consecutivo == Comprobantes::CONSECUTIVO_MENSUAL) {
            $consecutivoUsado->where('fecha_manual', $fecha_manual);
        }
        
        return $consecutivoUsado->count();
    }
}
