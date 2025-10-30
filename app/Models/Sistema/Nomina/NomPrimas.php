<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomPrimas extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_primas";

    protected $fillable = [
        "id_empleado",
        "fecha_inicio",
        "fecha_fin",
        "base",
        "dias",
        "promedio",
        "valor",
        "dias_promedio",
        "editado",
        "id_periodo_pago",
        "updated_by",
        "created_by"
	];
}
