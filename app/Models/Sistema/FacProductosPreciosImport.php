<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacProductosPreciosImport extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_productos_precios_imports";

    protected $fillable = [
        'id_producto',
        'row',
        'codigo',
        'nombre',
        'precio',
        'precio_inicial',
        'observacion',
        'estado'
    ];

    public function producto()
	{
		return $this->belongsTo('App\Models\Sistema\FacProductos', 'id_producto');
	}
}
