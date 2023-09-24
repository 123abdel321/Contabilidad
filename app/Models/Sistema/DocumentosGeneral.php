<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentosGeneral extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $fillable = [
        'id_nit',
        'id_cuenta',
        'id_comprobante',
        'id_centro_costos',
        'consecutivo',
        'documento_referencia',
        'debito',
        'credito',
        'saldo',
        'anulado',
        'concepto',
        'fecha_manual',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function relation()
    {
        return $this->morphTo();
    }

    public function nit()
    {
        return $this->belongsTo('App\Models\Sistema\Nits', 'id_nit');
	}

    public function cuenta()
    {
        return $this->belongsTo('App\Models\Sistema\PlanCuentas', 'id_cuenta');
	}

    public function comprobante()
	{
		return $this->belongsTo('App\Models\Sistema\Comprobantes', 'id_comprobante');
	}

    public function centro_costos()
    {
        return $this->belongsTo("App\Models\Sistema\CentroCostos", "id_centro_costos");
    }
    
}
