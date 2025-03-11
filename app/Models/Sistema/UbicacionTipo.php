<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UbicacionTipo extends Model
{
    protected $connection = 'sam';

    protected $table = "ubicacion_tipos";

    protected $fillable = [
        'id',
        'nombre'
    ];
}
