<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponenteFormasPago extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'componente_formas_pagos';

    protected $fillable = [
        'codigo',
        'nombre',
    ];
}
