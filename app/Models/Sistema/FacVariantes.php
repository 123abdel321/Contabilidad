<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacVariantes extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_variantes";

    protected $fillable = [
        'id',
        'nombre',
        'estado'
    ];

    public function opciones()
    {
        return $this->hasMany('App\Models\Sistema\FacVariantesOpciones', 'id_variante');
	}
}
