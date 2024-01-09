<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomConceptos extends Model
{
	use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_conceptos";

	protected $fillable = [
		"id",
		"tipo_concepto",
		"codigo",
		"nombre",
		"id_cuenta_administrativos",
		"id_cuenta_operativos",
		"id_cuenta_ventas",
		"id_cuenta_otros",
		"porcentaje",
		"id_concepto_porcentaje",
		"unidad",
		"valor_mensual",
		"concepto_fijo",
		"base_retencion",
		"base_sena",
		"base_icbf",
		"base_caja_compensacion",
		"base_salud",
		"base_pension",
		"base_arl",
		"base_vacacion",
		"base_prima",
		"base_cesantia",
		"base_interes_cesantia",
	];
}
