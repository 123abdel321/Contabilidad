<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacFormasPago extends Model
{
    use HasFactory;

    const TIPO_CUENTA_CAJA_BANCOS = 2;
    const TIPO_CUENTA_CXC = 3;
    const TIPO_CUENTA_CXP = 4;
    const TIPO_CUENTA_ANTICIPO_PROVEEDORES_XC = 7;
    const TIPO_CUENTA_ANTICIPO_CLIENTES_XP = 8;
    
    protected $connection = 'sam';

    protected $table = "fac_formas_pagos";

    protected $fillable = [ 
        'id_cuenta',
        'id_tipo_formas_pago',
        'nombre',
        'created_by',
        'updated_by'
    ];

    public function cuenta()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta");
	}

	public function tipoFormaPago()
	{
		return $this->belongsTo("App\Models\Sistema\FacTipoFormasPago", "id_tipo_formas_pago");
	}
}