<?php

namespace App\Scopes;

use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NotaDebitoScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
		$builder->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::NOTA_DEBITO);
    }
}
