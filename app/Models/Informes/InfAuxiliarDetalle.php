<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfAuxiliarDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_auxiliar_detalles";

    protected $fillable = [
        'id_auxiliar',
        'id_nit',
        'id_centro_costos',
        'id_comprobante',
        'numero_documento',
        'nombre_nit',
        'razon_social',
        'cuenta',
        'naturaleza_cuenta',
        'codigo_cecos',
        'nombre_cecos',
        'documento_referencia',
        'codigo_comprobante',
        'nombre_comprobante',
        'consecutivo',
        'concepto',
        'fecha_manual',
        'saldo_anterior',
        'debito',
        'credito',
        'saldo_final',
        'detalle',
        'detalle_group',
        'fecha_creacion',
        'fecha_edicion',
        'created_by',
        'updated_by',
    ];
}
