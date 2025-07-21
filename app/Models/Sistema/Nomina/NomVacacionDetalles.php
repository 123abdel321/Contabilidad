<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomVacacionDetalles extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_vacacion_detalles";

	protected $fillable = [
		"id_vacaciones",
		"concepto",
		"fecha",
		"valor"
	];
}
