<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "reservas";

    protected $fillable = [
        'id_nit',
        'id_ubicacion',
        'fecha_inicio',
        'fecha_fin',
        'observacion',
        'estado',
        'created_by',
        'updated_by',
    ];

    public function ubicacion() {
		return $this->belongsTo('App\Models\Sistema\Ubicacion', 'id_ubicacion', 'id');
	}

    public function nit() {
		return $this->belongsTo('App\Models\Sistema\Nits', 'id_nit', 'id');
	}
}