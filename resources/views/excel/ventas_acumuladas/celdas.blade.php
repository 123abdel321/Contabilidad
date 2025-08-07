<td style="{{ $style }}">
    @if ($documento->nivel == 1)
        {{ $documento->documento_referencia }}
    @endif
</td>
<td style="{{ $style }}">
    @if ($documento->nivel != 1)
        {{ $documento->numero_documento }} - {{ $documento->nombre_nit }}
    @endif
</td>
<td style="{{ $style }}">
    @if ($documento->nivel != 1)
        {{ $documento->codigo_bodega }} - {{ $documento->nombre_bodega }}
    @endif
</td>
<td style="{{ $style }}">
    @if ($documento->nivel != 1)
        {{ $documento->fecha_manual }}
    @endif
</td>
<td style="{{ $style }}">
    @if ($documento->nivel != 1)
        {{ $documento->codigo_producto }}
    @endif
</td>
<td style="{{ $style }}">
    @if ($documento->nivel != 1)
        {{ $documento->nombre_producto }}
    @endif
</td>
<td style="{{ $style }}">{{ $documento->cantidad }}</td>
<td style="{{ $style }}">{{ $documento->costo }}</td>
<td style="{{ $style }}">{{ $documento->subtotal }}</td>
<td style="{{ $style }}">{{ $documento->iva_porcentaje }}</td>
<td style="{{ $style }}">{{ $documento->iva_valor }}</td>
<td style="{{ $style }}">{{ $documento->descuento_porcentaje }}</td>
<td style="{{ $style }}">{{ $documento->descuento_valor }}</td>
<td style="{{ $style }}">{{ $documento->total }}</td>
<td style="{{ $style }}">{{ $documento->nombre_vendedor }}</td>