<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suscripciones extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'suscripciones';

    protected $fillable = [
        'nombre',
        'duracion',
        'descuento',
        'created_by',
        'updated_by'
    ];
}
