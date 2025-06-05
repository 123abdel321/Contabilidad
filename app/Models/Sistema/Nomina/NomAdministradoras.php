<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\Sistema\Nits;

class NomAdministradoras extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "nom_administradoras";

	protected $fillable = [
        'tipo',
        'codigo',
        'id_nit',
        'descripcion',
        'liquidada',
        'created_by',
        'updated_by'
    ];

    public function nit()
    {
        return $this->belongsTo(Nits::class, 'id_nit');
	}
}
