<?php

namespace App\Models\Sistema;

use App\Scopes\FacturaScope;
use App\Scopes\NotaCreditoScope;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;

class FacFacturaDevoluccion extends FacVentas
{
    protected static function boot()
	{
		parent::boot();

		static::addGlobalScope(new NotaCreditoScope);
	}

    protected $attributes = [
		'codigo_tipo_documento_dian' => CodigoDocumentoDianTypes::NOTA_CREDITO,
	];

    public function factura()
	{
		return $this->belongsTo(FacFactura::class, 'id_factura', 'id');
	}

	public function detalles()
	{
		return $this->hasMany(FacNotaCreditoDetalle::class, 'id_venta', 'id');
	}

	public function formasPago()
	{
		return $this->hasMany(FacNotaCreditoFormaPago::class, 'id_venta', 'id');
	}

	public function setCudeAttribute($value)
	{
		$this->attributes['fe_codigo_identificador'] = $value;
	}

	public function scopeWithCufe($query)
	{
		if (!$this->hasJoin($query, 'fac_movimientos_ventas')) {
			$query->join('fac_movimientos_ventas AS factura', 'factura.id', '=', 'fac_movimientos_ventas.id_factura');
		}

		return $query->addSelect('fac_movimientos_ventas.*', 'cufe');
	}

	private function hasJoin(Builder $builder, $table)
	{
		$joins = $builder->getQuery()->joins ?: [];

		foreach ($joins as $JoinClause) {
			if ($JoinClause->table == $table) {
				return true;
			}
		}

		return false;
	}
}
