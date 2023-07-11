<?php

namespace AppModels\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoImpuestos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "tipo_impuestos";

    protected $fillable = [
        'id',
        'codigo',
        'nombre',
        'es_retencion'
    ];

}
