<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InfResultado extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_resultados";

    protected $fillable = [
        'id',
        'id_empresa',
        'id_cecos',
        'id_nit',
        'cuenta_desde',
        'cuenta_hasta',
        'fecha_desde',
        'fecha_hasta',
        'exporta_excel',
        'archivo_excel',
        'tipo',
        'nivel',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function detalle(): BelongsToMany
    {
        return $this->belongsToMany(InfResultadoDetalle::class, 'id_resultado');
    }

}
