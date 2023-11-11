<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacMovimientoInventarioDetalles extends Model
{
    use HasFactory;
    
    protected $connection = 'sam';

    protected $table = "fac_movimiento_inventario_detalles";

    protected $fillable = [
        'id_movimiento_inventario',
        'id_producto',
        'cantidad',
        'costo',
        'total',
        'created_by',
        'updated_by',
    ];
}
