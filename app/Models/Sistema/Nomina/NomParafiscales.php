<?php

namespace App\Models\Sistema\Nomina;

use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\Sistema\Nits;

class NomParafiscales extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_parafiscales";

    protected $fillable = [
        "id",
        "id_empleado",
        "fecha",
        "concepto",
        "base",
        "porcentaje",
        "provision",
        "id_administradora",
        "id_cuenta_debito",
        "id_cuenta_credito",
        "editado"
	];
}

