<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfCarteraDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_cartera_detalles";

    protected $fillable = [
        "id_cartera",
        "id_nit",
        "id_cuenta",
        "cuenta",
        "nombre_cuenta",
        "naturaleza_cuenta",
        "numero_documento",
        "nombre_nit",
        "razon_social",
        "apartamento_nit",
        "documento_referencia",
        "total_facturas",
        "total_abono",
        "saldo",
        "nivel",
        "errores",
        "codigo_comprobante",
        "nombre_comprobante",
        "fecha_manual",
        "dias_cumplidos",
        "plazo",
        "concepto",
        "fecha_creacion",
        "fecha_edicion",
        "detalle",
        "detalle_group",
        "created_by",
        "updated_by",
    ];
}
