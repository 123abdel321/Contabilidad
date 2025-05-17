<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfResumenCarteraDetalle extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_resumen_cartera_detalles";

    protected $fillable = [
        'id_resumen_cartera',
        'id_nit',
        'nombre_nit',
        'numero_documento',
        'saldo_final',
        'dias_mora',
        'ubicacion',
        'cuenta_1',
        'cuenta_2',
        'cuenta_3',
        'cuenta_4',
        'cuenta_5',
        'cuenta_6',
        'cuenta_7',
        'cuenta_8',
        'cuenta_9',
        'cuenta_10',
        'cuenta_11',
        'cuenta_12',
        'cuenta_13',
        'cuenta_14',
        'cuenta_15',
        'cuenta_16',
        'cuenta_17',
        'cuenta_18',
        'cuenta_19',
        'cuenta_20'
    ];
}
