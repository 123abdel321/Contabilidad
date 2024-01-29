<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfEstadoActualDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_estado_actual_detalles";

    protected $fillable = [
        'id_estado_actual',
        'mes',
        'year',
        'comprobantes',
        'registros',
        'errores',
        'documentos',
        'total',
        'debito',
        'credito',
        'diferencia',
    ];
}
