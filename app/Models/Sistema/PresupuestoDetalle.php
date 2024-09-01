<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoDetalle extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "presupuesto_detalles";

    protected $fillable = [
        'id',
        'id_presupuesto',
        'id_padre',
        'es_grupo',
        'cuenta',
        'nombre',
        'presupuesto',
        'diferencia',
        'enero',
        'febrero',
        'marzo',
        'abril',
        'mayo',
        'junio',
        'julio',
        'agosto',
        'septiembre',
        'octubre',
        'noviembre',
        'diciembre',
        'auxiliar',
    ];
}
