<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacVentaPagos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = 'fac_venta_pagos';

    protected $fillable = [
        'id_venta',
        'id_forma_pago',
        'valor',
        'saldo',
        'created_by',
        'updated_by',
    ];

    public function venta()
	{
		return $this->belongsTo(FacVentas::class, 'id_venta');
	}

    public function forma_pago()
	{
		return $this->belongsTo(FacFormasPago::class, 'id_forma_pago');
	}
}
