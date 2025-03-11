<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    protected $connection = 'sam';

    protected $table = "ubicacions";

    protected $fillable = [
        'id',
        'codigo',
        'nombre',
        'id_ubicacion_tipos',
        'created_by',
        'updated_by'
    ];

    public function tipo()
    {
        return $this->belongsTo(UbicacionTipo::class, "id_ubicacion_tipos");
    }
}
