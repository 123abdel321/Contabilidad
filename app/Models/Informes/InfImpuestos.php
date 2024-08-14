<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InfImpuestos extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_impuestos";

    protected $fillable = [
        'id',
        'id_empresa',
        'id_cuenta',
        'id_nit',
        'fecha_hasta',
        'fecha_desde',
        'detallar_impuestos',
        'agrupar_impuestos',
        'nivel',
        'tipo_informe',
        'exporta_excel',
        'archivo_excel',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function detalle(): BelongsToMany
    {
        return $this->belongsToMany(InfImpuestosDetalle::class, 'id_cartera');
    }
}
