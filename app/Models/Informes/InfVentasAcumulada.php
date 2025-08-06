<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfVentasAcumulada extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_ventas_acumuladas";

    protected $fillable = [
        'id_empresa',
        'fecha_desde',
        'fecha_hasta',
        'id_tipo_informe',
        'id_nit',
        'id_resolucion',
        'id_bodega',
        'id_producto',
        'id_usuario',
        'documento_referencia',
        'id_forma_pago',
        'detallar_venta',
        'exporta_excel',
        'archivo_excel',
        'estado',
        'created_by',
        'updated_by'
    ];
}
