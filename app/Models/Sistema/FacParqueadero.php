<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacParqueadero extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_parqueaderos";

    protected $fillable = [
        'id_cliente',
        'id_bodega',
        'id_centro_costos',
        'id_vendedor',
        'id_venta',
        'id_producto',
        'placa',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'consecutivo',
        'subtotal',
        'total_iva',
        'total_descuento',
        'total_rete_fuente',
        'total_cambio',
        'porcentaje_rete_fuente',
        'total_factura',
        'estado',
        'created_by',
        'updated_by'
    ];

    public function venta()
	{
		return $this->hasMany(FacVentas::class, 'id', 'id_venta');
	}

    public function bodega()
    {
        return $this->belongsTo(FacBodegas::class, 'id_bodega');
	}

    public function cliente()
    {
        return $this->belongsTo(Nits::class, 'id_cliente');
	}

    public function centro_costo()
    {
        return $this->belongsTo(CentroCostos::class, 'id_centro_costos');
	}

    public function vendedor()
    {
        return $this->belongsTo(FacVendedores::class, 'id_vendedor');
	}

    public function producto()
	{
		return $this->belongsTo(FacProductos::class, 'id_producto', 'id');
	}
}
