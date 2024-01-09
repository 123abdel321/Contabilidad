<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacVariantesOpciones extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_variantes_opciones";

    protected $fillable = [
        'id',
        'id_variante',
        'nombre',
        'estado'
    ];

    public function variante()
    {
        return $this->hasOne(FacVariantes::class, 'id', 'id_variante');
	}
}
