<?php

namespace App\Models\Sistema;

class FacFacturaDetalle extends FacVentaDetalles
{
    public function factura()
	{
		return $this->belongsTo(FacFactura::class, 'id_venta', 'id');
	}

	public function producto()
	{
		return $this->belongsTo(FacProductos::class, 'id_producto', 'id');
	}
}
