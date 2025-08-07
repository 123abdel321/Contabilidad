<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfVentasAcumuladaDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_ventas_acumulada_detalles";

    protected $fillable = [
        'id_venta_acumulada',
        'id_nit',
        'id_cuenta',
        'id_usuario',
        'id_comprobante',
        'id_centro_costos',
        'cuenta',
        'nombre_cuenta',
        'numero_documento',
        'nombre_nit',
        'nombre_vendedor',
        'razon_social',
        'codigo_cecos',
        'nombre_cecos',
        'codigo_comprobante',
        'nombre_comprobante',
        'codigo_bodega',
        'nombre_bodega',
        'codigo_producto',
        'nombre_producto',
        'documento_referencia',
        'consecutivo',
        'observacion',
        'fecha_manual',
        'cantidad',
        'costo',
        'subtotal',
        'descuento_porcentaje',
        'rete_fuente_porcentaje',
        'descuento_valor',
        'iva_porcentaje',
        'iva_valor',
        'total',
        'nivel',
        'fecha_creacion',
        'fecha_edicion',
        'created_by',
        'updated_by'
    ];
}
