<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConReciboPagos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = 'con_recibo_pagos';

    protected $fillable = [
        'id_recibo',
        'id_forma_pago',
        'valor',
        'saldo',
        'created_by',
        'updated_by',
    ];
}
