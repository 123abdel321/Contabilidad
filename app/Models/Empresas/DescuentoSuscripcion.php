<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Model;

class DescuentoSuscripcion extends Model
{
    protected $connection = 'clientes';
    protected $table = 'descuentos_suscripcions';

    protected $fillable = [
        'meses',
        'descuento_porcentaje',
        'activo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'descuento_porcentaje' => 'float',
        'activo' => 'boolean',
    ];
}