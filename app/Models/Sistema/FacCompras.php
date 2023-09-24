<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacCompras extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_compras";

    protected $fillable = [
        'id_proveedor',
        'id_comprobante',
        'id_bodega',
        'id_centro_costos',
        'fecha_manual',
        'consecutivo',
        'documento_referencia',
        'subtotal',
        'total_iva',
        'total_descuento',
        'total_rete_fuente',
        'porcentaje_rete_fuente',
        'total_factura',
        'observacion',
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

    public function proveedor()
    {
        return $this->belongsTo('App\Models\Sistema\Nits', 'id_proveedor');
	}

    public function comprobante()
	{
		return $this->belongsTo('App\Models\Sistema\Comprobantes', 'id_comprobante');
	}

    public function detalles()
	{
		return $this->hasMany('App\Models\Sistema\FacCompraDetalles', 'id_compra');
	}
}
