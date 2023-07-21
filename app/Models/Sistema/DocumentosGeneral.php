<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sistema\Comprobantes;

class DocumentosGeneral extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $fillable = [
        'id_nit',
        'id_cuenta',
        'id_comprobante',
        'id_centro_costos',
        'auxiliar',
        'fecha_manual',
        'consecutivo',
        'documento_referencia',
        'debito',
        'credito',
        'saldo',
        'concepto',
        'anulado',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function relation()
    {
        return $this->morphTo();
    }

    public function nit(){
        return $this->belongsTo('App\Models\Sistema\Nits', 'id_nit');
	}

    public function cuenta(){
        return $this->belongsTo('App\Models\Sistema\PlanCuentas', 'id_cuenta');
	}

    public function centro_costos()
    {
        return $this->belongsTo("App\Models\Sistema\CentroCostos", "id_centro_costos");
    }

    public function comprobante()
	{
		return $this->belongsTo('App\Models\Sistema\Comprobantes', 'id_comprobante');
	}

    public function scopeWhereDocumento($query, $idComprobante, $consecutivo, $fecha = null)
	{
		$comprobante = Comprobantes::find($idComprobante);
		$isComprobanteMensual = $comprobante->tipo_consecutivo && Comprobantes::CONSECUTIVO_MENSUAL;

		$query->where('id_comprobante', $idComprobante)
			->where('consecutivo', $consecutivo)
			->when($fecha && $isComprobanteMensual, function ($query) use ($fecha) {
				$query->where('fecha_manual', 'like', substr($fecha, 0, 7) . '%');
			});
	}
}
