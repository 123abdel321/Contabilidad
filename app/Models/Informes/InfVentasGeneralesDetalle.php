<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfVentasGeneralesDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_ventas_generales_detalles";

    protected $fillable = [
        'id_venta_general',
        'id_nit',
        'id_cuenta',
        'id_usuario',
        'id_comprobante',
        'id_centro_costos',
        'cuenta',
        'nombre_cuenta',
        'numero_documento',
        'nombre_nit',
        'razon_social',
        'codigo_cecos',
        'nombre_cecos',
        'codigo_comprobante',
        'nombre_comprobante',
        'documento_referencia',
        'consecutivo',
        'concepto',
        'fecha_manual',
        'debito',
        'credito',
        'total',
        'nivel',
        'fecha_creacion',
        'fecha_edicion',
    ];
}
