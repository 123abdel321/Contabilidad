<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NitsImport extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nits_imports";

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'digito_verificacion',
        'primer_nombre',
        'otros_nombres',
        'primer_apellido',
        'segundo_apellido',
        'razon_social',
        'direccion',
        'email',
        'telefono_1',
        'plazo',
        'cupo',
        'observaciones',
    ];
}
