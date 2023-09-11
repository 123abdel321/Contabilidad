<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacProductosVariantes extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_productos_variantes";

    protected $fillable = [
        'id',
        'id_variante',
        'id_producto',
        'id_producto_padre',
        'id_variante_opcion'
    ];

    public function producto()
    {
        return $this->belongsTo("App\Models\Sistema\FacProductos", "id_producto");
    }

    public function producto_padre()
    {
        return $this->belongsTo("App\Models\Sistema\FacProductos", "id_producto_padre");
    }

    public function variante()
    {
        return $this->belongsTo("App\Models\Sistema\FacVariantes", "id_variante");
    }

    public function opcion()
    {
        return $this->belongsTo("App\Models\Sistema\FacVariantesOpciones", "id_variante");
    }
}
