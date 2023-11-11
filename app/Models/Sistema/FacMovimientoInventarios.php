<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacMovimientoInventarios extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_movimiento_inventarios";

    protected $fillable = [
        'id_nit',
        'id_cargue_descargues',
        'id_comprobante',
        'id_centro_costos',
        'id_cuenta_debito',
        'id_cuenta_credito',
        'id_bodega_origen',
        'id_bodega_destino',
        'tipo',
        'cantidad',
        'total_movimiento',
        'consecutivo',
        'fecha_manual',
        'created_by',
        'updated_by',
    ];

    public function documentos()
    {
        return $this->morphMany('App\Models\Sistema\DocumentosGeneral', 'relation');
	}

    public function bodegas()
    {
        return $this->morphMany('App\Models\Sistema\FacProductosBodegasMovimiento', 'relation');
	}
}
