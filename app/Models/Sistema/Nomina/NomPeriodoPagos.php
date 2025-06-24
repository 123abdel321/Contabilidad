<?php

namespace App\Models\Sistema\Nomina;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\Sistema\Nits;

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

    protected $appends = [
        'fecha_inicio_periodo_formatted',
        'fecha_fin_periodo_formatted'
    ];

    public function getFechaInicioPeriodoFormattedAttribute()
    {
        return Carbon::parse($this->fecha_inicio_periodo)->format('d-M-Y');
    }

    public function getFechaFinPeriodoFormattedAttribute()
    {
        return Carbon::parse($this->fecha_fin_periodo)->format('d-M-Y');
    }

    public function empleado()
	{
		return $this->belongsTo(Nits::class, 'id_empleado', 'id');
	}

    public function detalles()
	{
		return $this->hasMany(NomPeriodoPagoDetalles::class, 'id_periodo_pago', 'id');
	}

	public function novedades()
	{
		return $this->hasMany(NomNovedadesGenerales::class, 'id_periodo_pago', 'id');
	}

    public function sumDetalles()
	{
		return $this->hasOne(NomPeriodoPagoDetalles::class, 'id_periodo_pago', 'id')->select([
			'id',
			'id_periodo_pago',
			DB::raw('SUM(IF (valor >= 0, valor, 0)) AS devengados'),
			DB::raw('SUM(IF (valor < 0, valor, 0)) AS deducciones'),
			DB::raw('SUM(valor) AS neto')
		])
			->groupBy('id_periodo_pago');
	}
}

