<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacCompraDetalles extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_compra_detalles";

    protected $fillable = [
        'id_compra',
        'id_cuenta_compra',
        'id_cuenta_compra_retencion',
        'id_cuenta_compra_iva',
        'id_cuenta_compra_descuento',
        'descripcion',
        'cantidad',
        'costo',
        'subtotal',
        'descuento_porcentaje',
        'descuento_valor',
        'iva_porcentaje',
        'iva_valor',
        'total',
        'created_by',
        'updated_by',
    ];
}
