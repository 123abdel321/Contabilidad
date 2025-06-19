<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfExtracto extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_extractos";

    protected $fillable = [
        'id',
        'id_empresa',
        'id_nit',
        'documento_referencia',
        'fecha_desde',
        'fecha_hasta',
        'errores',
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
        return $this->belongsToMany(InfExtractoDetalle::class, 'id_extracto', 'id');
    }
}
