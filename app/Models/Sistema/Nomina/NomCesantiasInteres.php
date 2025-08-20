<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomCesantiasInteres extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_cesantias_interes";

	protected $fillable = [
        'id_empleado',
        'id_periodo_pago',
        'fecha_inicio',
        'fecha_fin',
        'base',
        'dias',
        'promedio',
        'cesantias',
        'intereses',
        'editado',
        'updated_by',
        'created_by'
    ];
}
