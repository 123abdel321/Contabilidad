<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponentesSuscripcion extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'componentes_suscripcions';

    protected $fillable = [
        'id_padre',
        'nombre',
        'tipo',
        'rango_desde',
        'rango_hasta',
        'precio',
        'automatico',
        'descuento',
        'created_by',
        'updated_by'
    ];
}
