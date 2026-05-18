<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomElectronica extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_electronicas";

	const TIPO_INDIVIDUAL = 0;
	const TIPO_INDIVIDUAL_AJUSTE = 1;

    protected $fillable = [
		"id",
		"id_empleado",
		"cune",
		"mes",
		"tipo"
	];
}
