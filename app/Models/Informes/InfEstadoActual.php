<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfEstadoActual extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_estado_actuales";

    protected $fillable = [
        'id_empresa',
        'id_comprobante',
        'year',
    ];
}