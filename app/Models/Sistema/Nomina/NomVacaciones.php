<?php

namespace App\Models\Sistema\Nomina;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\Sistema\Nits;

class NomVacaciones extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_vacaciones";

    protected $fillable = [
        "id_empleado",
        "id_contrato",
        "metodo",
        "fecha_inicio",
        "fecha_fin",
        "dias_habiles",
        "dias_compensados",
        "dias_no_habiles",
        "promedio_otros",
        "salario_dia",
        "valor_dia_vacaciones",
        "total_disfrutado",
        "total_compensado",
        "observacion",
        "salario_base",
        "updated_by",
        "created_by",
	];

    public function novedades()
    {
        return $this->morphMany(NomNovedadesGenerales::class, 'relation');
	}

    public function contrato()
    {
        return $this->belongsTo(NomContratos::class, 'id_contrato', 'id');
	}

    public function empleado()
    {
        return $this->belongsTo(Nits::class, 'id_empleado', 'id');
	}

}

