<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomPeriodoPagos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_periodo_pagos";

    const ESTADO_PENDIENTE = 0;
	const ESTADO_CAUSADO = 1;
	const ESTADO_PAGADO = 2;

    protected $fillable = [
        'id_empleado',
        'id_contrato',
        'fecha_inicio_periodo',
        'fecha_fin_periodo',
        'estado',
        'created_by',
        'updated_by',
    ];
}

