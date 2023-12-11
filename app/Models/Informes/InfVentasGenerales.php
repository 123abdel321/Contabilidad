<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InfVentasGenerales extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_ventas_generales";

    protected $fillable = [
        'id',
        'id_empresa',
        'fecha_desde',
        'fecha_hasta',
        'precio_desde',
        'precio_hasta',
        'id_nit',
        'id_cuenta',
        'id_usuario',
        'id_bodega',
        'id_resolucion',
        'consecutivo',
        'exporta_excel',
        'archivo_excel'
    ];

    public function detalle(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Informes\InfVentasGeneralesDetalle', 'id_venta_general');
    }
}
