<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InfDocumentosGenerales extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_documentos_generales";

    protected $fillable = [
        'id',
        'id_empresa',
        'fecha_desde',
        'fecha_hasta',
        'id_nit',
        'id_cuenta',
        'id_usuario',
        'id_comprobante',
        'id_centro_costos',
        'documento_referencia',
        'consecutivo',
        'concepto',
        'agrupar',
        'agrupado',
        'exporta_excel',
        'archivo_excel'
    ];

    public function detalle(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Informes\InfDocumentosGeneralesDetalle', 'id_documentos_generales');
    }
}
