<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfResumenComprobanteDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_resumen_comprobante_detalles";

    protected $fillable = [
        'id_resumen_comprobante',
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
        'apartamento_nit',
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
        'diferencia',
        'registros',
        'nivel',
        'fecha_creacion',
        'fecha_edicion',
        'created_by',
        'updated_by',
    ];
}
