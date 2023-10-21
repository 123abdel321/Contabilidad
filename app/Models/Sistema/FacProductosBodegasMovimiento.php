<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacProductosBodegasMovimiento extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_productos_bodegas_movimientos";

    protected $fillable = [
        'id_producto',
        'id_bodega',
        'cantidad_anterior',
        'cantidad',
        'tipo_tranferencia',
        'inventario',
        'created_by',
        'updated_by',
    ];

    public function relation()
    {
        return $this->morphTo();
    }
}
