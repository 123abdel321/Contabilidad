<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\Sistema\Nits;

class NomNovedadesGenerales extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_novedades_generales";

    protected $fillable = [
        'relation_id',
        'relation_type',
        'id_empleado',
        'id_periodo_pago',
        'id_concepto',
        'tipo_unidad',
        'unidades',
        'valor',
        'porcentaje',
        'base',
        'observacion',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'created_by',
        'updated_by',
    ];

    public function relation()
    {
        return $this->morphTo();
    }

    public function empleado()
	{
		return $this->belongsTo(Nits::class, 'id_empleado', 'id');
	}

    public function periodo_pago()
	{
		return $this->belongsTo(NomPeriodoPagos::class, 'id_periodo_pago', 'id');
	}

	public function concepto()
	{
		return $this->belongsTo(NomConceptos::class, 'id_concepto', 'id');
	}
    
}
