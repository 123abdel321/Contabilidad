<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponsabilidadesTributarias extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = "responsabilidades_tributarias";

    protected $fillable = [
        'codigo',
        'nombre'
    ];
    
}
