<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacProductosCombos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_productos_combos";

    protected $fillable = [
        'id',
        'id_combo',
        'id_producto',
        'costo',
        'cantidad',
    ];
}
