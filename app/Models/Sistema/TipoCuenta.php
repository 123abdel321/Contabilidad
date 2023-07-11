<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCuenta extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "tipo_cuentas";

    protected $fillable = [
        'id',
        'nombre',
    ];
}
