<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadesEconomicas extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = "actividades_economicas";

    protected $fillable = [
        'codigo',
        'nombre',
        'porcentaje'
    ];
    
}
