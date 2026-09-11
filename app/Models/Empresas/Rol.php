<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'roles';

    protected $fillable = [
        'id',
        'nombre',
    ];
}
