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
        'id_factura',
        'id_vendedor',
        'fecha_manual',
        'consecutivo',
        'documento_referencia',
        'subtotal',
        'total_iva',
        'total_descuento',
        'total_rete_fuente',
        'total_cambio',
        'porcentaje_rete_fuente',
        'total_factura',
        'observacion',
        'codigo_tipo_documento_dian',
        'fe_codigo_identificador',
        'fe_fecha_validacion',
        'fe_fecha_envio_correo',
        'fe_estado_acuse',
        'fe_codigo_qr',
        'fe_zip_key',
        'fe_xml_file',
        'created_by',
        'updated_by'
    ];

    public function documentos()
    {
        return $this->morphMany(DocumentosGeneral::class, 'relation');
	}

    public function bodegas()
    {
        return $this->morphMany(FacProductosBodegasMovimiento::class, 'relation');
	}

    public function bodega()
    {
        return $this->belongsTo(FacBodegas::class, 'id_bodega');
	}

    public function centro_costo()
    {
        return $this->belongsTo(CentroCostos::class, 'id_centro_costos');
	}

    public function cliente()
    {
        return $this->belongsTo(Nits::class, 'id_cliente');
	}

    public function vendedor()
    {
        return $this->belongsTo(FacVendedores::class, 'id_vendedor');
	}

    public function comprobante()
	{
		return $this->belongsTo(Comprobantes::class, 'id_comprobante');
	}

    public function detalles()
	{
		return $this->hasMany(FacVentaDetalles::class, 'id_venta');
	}

    public function pagos()
	{
		return $this->hasMany(FacVentaPagos::class, 'id_venta');
	}

    public function resolucion()
	{
		return $this->belongsTo(FacResoluciones::class, 'id_resolucion');
	}

    public function factura()
    {
        return $this->belongsTo(FacVentas::class, 'id_factura');
    }

    public function getDocumentoReferenciaAttribute()
	{
		return $this->resolucion ? "{$this->resolucion->prefijo}-{$this->consecutivo}" : '';
	}

	public function getDocumentoReferenciaFeAttribute()
	{
		return $this->resolucion ? "{$this->resolucion->prefijo}{$this->consecutivo}" : '';
	}

    public function getFechaValidacionAttribute()
	{
		if (!$this->fe_fecha_validacion) return '';

		return date('Y-m-d', strtotime($this->fe_fecha_validacion));
	}

    public function getFechaVencimientoAttribute()
	{
		$previousLoaded = $this->relationLoaded('cliente');

		$this->loadMissing('cliente');

		if (!$this->cliente) return '';
        $plazo = $this->cliente->plazo ?? 30;
		$fechaVencimiento = date('Y-m-d', strtotime($this->fecha_manual . " + 30 days"));

		if ($previousLoaded) {
			$this->unsetRelation('cliente');
		}

		return $fechaVencimiento;
	}

    public function getCufeAttribute()
	{
		return $this->fe_codigo_identificador;
	}

	public function setCufeAttribute($value)
	{
		$this->attributes['fe_codigo_identificador'] = $value;
	}
}
