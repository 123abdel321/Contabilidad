<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class InfImpuestosDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_impuestos_detalles";

    protected $fillable = [
        "id_impuestos",
        "id_nit",
        "id_cuenta",
        "id_centro_costos",
        "id_comprobante",
        "cuenta",
        "nombre_cuenta",
        "numero_documento",
        "nombre_nit",
        "razon_social",
        "documento_referencia",
        "saldo_anterior",
        "debito",
        "credito",
        "valor_base",
        "porcentaje_base",
        "nivel",
        "errores",
        "codigo_comprobante",
        "nombre_comprobante",
        "fecha_manual",
        "concepto",
        "fecha_creacion",
        "fecha_edicion",
        "detalle",
        "detalle_group",
        "created_by",
        "updated_by",
    ];

    public function nit()
    {
        return $this->belongsTo("App\Models\Sistema\Nits", 'id_nit');
	}
}
