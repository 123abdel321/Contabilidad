<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacProductos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_productos";

    protected $fillable = [
        'id',
        'id_padre',
        'id_familia',
        'tipo_producto',
        'codigo',
        'nombre',
        'precio',
        'precio_inicial',
        'precio_minimo',
        'variante',
        'estado',
        'created_by',
        'updated_by'
    ];

    public function bodegas()
    {
        return $this->morphMany('App\Models\Sistema\FacProductosBodegasMovimiento', 'relation');
	}

    public function variantes()
    {
        return $this->hasMany('App\Models\Sistema\FacProductosVariantes', 'id_producto');
	}

    public function inventarios()
    {
        return $this->hasMany('App\Models\Sistema\FacProductosBodegas', 'id_producto');
	}

    public function familia()
    {
        return $this->belongsTo("App\Models\Sistema\FacFamilias", "id_familia");
    }

    public function hijos()
    {
        return $this->hasMany('App\Models\Sistema\FacProductos', 'id_padre');
	}

}
