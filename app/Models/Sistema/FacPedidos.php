<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacPedidos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_pedidos";

    protected $fillable = [
        'id_cliente',
        'id_bodega',
        'id_centro_costos',
        'id_ubicacion',
        'id_vendedor',
        'id_venta',
        'consecutivo',
        'subtotal',
        'total_iva',
        'total_descuento',
        'total_rete_fuente',
        'total_cambio',
        'porcentaje_rete_fuente',
        'estado',
        'total_factura',
        'created_by',
        'updated_by',
    ];

    public function bodega()
    {
        return $this->belongsTo(FacBodegas::class, 'id_bodega');
	}

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion');
	}

    public function centro_costo()
    {
        return $this->belongsTo(CentroCostos::class, 'id_centro_costos');
	}

    public function cliente()
    {
        return $this->belongsTo(Nits::class, 'id_cliente');
	}

    public function vendedor()
    {
        return $this->belongsTo(FacVendedores::class, 'id_vendedor');
	}

    public function detalles()
	{
		return $this->hasMany(FacPedidoDetalles::class, 'id_pedido');
	}

    public function venta()
	{
		return $this->hasMany(FacVentas::class, 'id', 'id_venta');
	}
}
