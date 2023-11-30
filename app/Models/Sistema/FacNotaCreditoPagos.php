<?php

namespace App\Models\Sistema;

class FacNotaCreditoPagos extends FacVentaPagos
{
    public function notaDebito()
    {
        return $this->belongsTo(FacNotaDebito::class, 'id_factura', 'id');
	}
}
