<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomPeriodos extends Model
{
    use HasFactory;

    const TIPO_DIA_PAGO_ORDINAL = 0;
	const TIPO_DIA_PAGO_CALENDARIO = 1;

    protected $connection = 'sam';

    protected $table = "nom_periodos";

    protected $fillable = [
        'nombre',
        'dias_salario',
        'horas_dia',
        'tipo_dia_pago',
        'periodo_dias_ordinales',
        'periodo_dias_calendario',
        'created_by',
        'updated_by'
    ];

    public function contratos()
	{
		return $this->hasMany(NomContratos::class, 'id_periodo', 'id');
	}
    
}
