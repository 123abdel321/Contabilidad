<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacBodegas extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_bodegas";

    protected $fillable = [ 
        'codigo',
        'nombre',
        'ubicacion',
        'id_centro_costos',
        'id_responsable',
        'created_by',
        'updated_by',
    ];

    public function cecos()
    {
        return $this->belongsTo("App\Models\Sistema\CentroCostos", "id_centro_costos");
    }
}
