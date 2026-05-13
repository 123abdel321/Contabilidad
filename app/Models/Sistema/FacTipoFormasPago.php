<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacTipoFormasPago extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_tipo_formas_pagos";

    public const EFECTIVO = 10;
    public const CHEQUE = 20;

    protected $fillable = [ 
        'codigo',
        'nombre'
    ];
}


