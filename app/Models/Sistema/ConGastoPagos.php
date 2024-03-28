<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConGastoPagos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "con_gasto_pagos";

    protected $fillable = [
        'id_gasto',
        'id_forma_pago',
        'valor',
        'saldo',
        'created_by',
        'updated_by'
    ];
}
