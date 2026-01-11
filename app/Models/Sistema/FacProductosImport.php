<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\FacFamilias;

class FacProductosImport extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_productos_imports";

    protected $fillable = [
        'id_producto',
        'row',
        'codigo',
        'nombre',
        'id_familia',
        'id_bodega',
        'costo',
        'venta',
        'existencias',
        'observacion',
        'estado'
    ];

    public function familia()
    {
        return $this->belongsTo(FacFamilias::class, "id_familia");
    }

    public function bodega()
    {
        return $this->belongsTo(FacBodegas::class, "id_bodega");
    }
}
