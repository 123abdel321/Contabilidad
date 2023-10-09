<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacVentas extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_ventas";

    protected $fillable = [
        'id_cliente',
        'id_resolucion',
        'id_comprobante',
        'id_centro_costos',
        'id_bodega',
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
        'codigo_tipo_documento_dian',
        'fe_codigo_identificador',
        'fe_fecha_validacion',
        'fe_fecha_envio_correo',
        'fe_estado_acuse',
        'fe_codigo_qr',
        'created_by',
        'updated_by'
    ];

    public function documentos()
    {
        return $this->morphMany('App\Models\Sistema\DocumentosGeneral', 'relation');
	}

    public function bodegas()
    {
        return $this->morphMany('App\Models\Sistema\FacProductosBodegasMovimiento', 'relation');
	}

    public function bodega()
    {
        return $this->belongsTo('App\Models\Sistema\FacBodegas', 'id_bodega');
	}

    public function cliente()
    {
        return $this->belongsTo('App\Models\Sistema\Nits', 'id_cliente');
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
