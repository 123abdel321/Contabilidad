<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfEstadoComprobanteDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_estado_comprobante_detalles";

    protected $fillable = [
        'id_estado_comprobante',
        'codigo_comprobante',
        'nombre_comprobante',
        'year',
        'documentos',
        'registros',
        'debito',
        'credito',
        'diferencia',
        'nombre_tipo_comprobante',
        'errores',
        'total',
        'created_by',
        'updated_by'
    ];
}
