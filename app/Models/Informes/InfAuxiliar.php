<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InfAuxiliar extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_auxiliars";

    protected $fillable = [
        'id',
        'id_empresa',
        'id_nit',
        'id_cuenta',
        'fecha_desde',
        'fecha_hasta',
        'estado',
        'exporta_excel',
        'archivo_excel',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function detalle(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Informes\InfAuxiliarDetalle', 'id_auxiliar', 'id');
    }
}
