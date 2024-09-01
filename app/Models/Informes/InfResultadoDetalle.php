<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfResultadoDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_resultado_detalles";

    protected $fillable = [
        "id_resultado",
        "id_nit",
        "id_cuenta",
        "cuenta",
        "nombre_cuenta",
        "numero_documento",
        "nombre_nit",
        "razon_social",
        "saldo_anterior",
        "debito",
        "credito",
        "saldo",
        "ppto_anterior",
        "ppto_movimiento",
        "ppto_acumulado",
        "ppto_diferencia",
        "ppto_porcentaje",
        "ppto_porcentaje_acumulado",
        "nivel",
        "errores",
        "fecha_creacion",
        "fecha_edicion",
        "created_by",
        "updated_by",
    ];
}
