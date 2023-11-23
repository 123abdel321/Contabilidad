<?php

namespace App\Models\Sistema;

use App\Scopes\FacturaScope;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;

class FacFactura extends FacVentas
{
    protected static function boot()
	{
		parent::boot();

		static::addGlobalScope(new FacturaScope);
	}

    protected $attributes = [
		'codigo_tipo_documento_dian' => CodigoDocumentoDianTypes::VENTA_NACIONAL,
	];

    public function notasCredito()
	{
		return $this->hasMany(FacNotaCredito::class, 'id_factura', 'id')
			->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::NOTA_CREDITO);
	}

	public function detalles()
	{
		return $this->hasMany(FacFacturaDetalle::class, 'id_venta', 'id');
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
