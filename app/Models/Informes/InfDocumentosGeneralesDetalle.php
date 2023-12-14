<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfDocumentosGeneralesDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_documentos_generales_detalles";

    protected $fillable = [
        'id_documentos_generales',
        'id_nit',
        'id_cuenta',
        'id_usuario',
        'id_comprobante',
        'id_centro_costos',
        'cuenta',
        'nombre_cuenta',
        'base_cuenta',
        'porcentaje_cuenta',
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
        'diferencia',
        'nivel',
        'fecha_creacion',
        'fecha_edicion',
    ];
}
