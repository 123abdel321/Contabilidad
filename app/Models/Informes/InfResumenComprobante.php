<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfResumenComprobante extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_resumen_comprobantes";

    protected $fillable = [
        'id_empresa',
        'fecha_desde',
        'fecha_hasta',
        'id_comprobante',
        'id_cuenta',
        'id_nit',
        'agrupado',
        'detalle',
        'created_by',
        'updated_by',
    ];
}
