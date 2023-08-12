<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfBalanceDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_balance_detalles";

    protected $fillable = [
        'id_balance',
        'id_cuenta',
        'cuenta',
        'nombre_cuenta',
        'saldo_anterior',
        'debito',
        'credito',
        'saldo_final',
        'fecha_creacion',
        'fecha_edicion',
        'created_by',
        'updated_by'
    ];
}
