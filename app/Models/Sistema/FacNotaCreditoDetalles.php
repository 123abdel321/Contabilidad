<?php

namespace App\Models\Sistema;

class FacNotaCreditoDetalles extends FacVentaDetalles
{
    public function notaDebito()
    {
        return $this->belongsTo(FacNotaDebito::class, 'id_factura', 'id');
	}
}
