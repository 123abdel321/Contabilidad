<?php

namespace App\Scopes;

use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FacturaScope implements Scope
{
	public function apply(Builder $builder, Model $model)
	{
		$builder->whereIn('codigo_tipo_documento_dian', [CodigoDocumentoDianTypes::VENTA_NACIONAL, CodigoDocumentoDianTypes::VENTA_EXPORTACION]);
	}
}
