<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacPedidoDetalles extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_pedido_detalles";

    protected $fillable = [
        'id_pedido',
        'id_producto',
        'id_cuenta_venta',
        'id_cuenta_venta_retencion',
        'id_cuenta_venta_iva',
        'id_cuenta_venta_descuento',
        'descripcion',
        'cantidad',
        'costo',
        'subtotal',
        'descuento_porcentaje',
        'descuento_valor',
        'iva_porcentaje',
        'iva_valor',
        'total',
        'observacion',
        'created_by',
        'updated_by',
    ];

    public function pedido()
	{
		return $this->belongsTo(FacPedidos::class, 'id_pedido');
	}

    public function producto()
	{
		return $this->belongsTo(FacProductos::class, 'id_producto', 'id');
	}

    public function cuenta_iva()
	{
		return $this->belongsTo(PlanCuentas::class, 'id_cuenta_venta_iva', 'id');
	}

    public function cuenta_retencion()
	{
		return $this->belongsTo(PlanCuentas::class, 'id_cuenta_venta_retencion', 'id');
	}
}
