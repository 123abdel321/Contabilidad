<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacProductosBodegas extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_productos_bodegas";

    protected $fillable = [
        'id',
        'id_producto',
        'id_bodega',
        'cantidad',
        'tipo_tranferencia',
        'created_by',
        'updated_by'
    ];

    public function producto()
    {
        return $this->belongsTo("App\Models\Sistema\FacProductos", "id_producto");
    }

    public function bodega()
    {
        return $this->belongsTo("App\Models\Sistema\FacBodegas", "id_bodega");
    }
}
