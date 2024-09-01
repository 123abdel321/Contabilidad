<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfEstadoComprobante extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_estado_comprobantes";

    protected $fillable = [
        'id_empresa',
        'year',
        'month',
    ];
}
