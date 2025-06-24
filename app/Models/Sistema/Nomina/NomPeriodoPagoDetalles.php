<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomPeriodoPagoDetalles extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_periodo_pago_detalles";

	protected $fillable = [
		'id',
		'id_periodo_pago',
		'id_concepto',
		'tipo_unidad',
		'unidades',
		'valor',
		'porcentaje',
		'base',
		'observacion',
		'fecha_inicio',
		'fecha_fin',
		'hora_inicio',
		'hora_fin'
	];

	const TIPO_UNIDAD_HORAS = 0;
	const TIPO_UNIDAD_DIAS = 1;
	const TIPO_UNIDAD_VALOR = 2;

    public function periodoPago()
	{
		return $this->belongsTo(NomPeriodoPagos::class, 'id_periodo_pago', 'id');
	}

	public function concepto()
	{
		return $this->belongsTo(NomConceptos::class, 'id_concepto', 'id');
	}
}
