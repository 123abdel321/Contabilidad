<td style="{{ $style }}">{{ $auxiliar->cuenta }}</td>
<td style="{{ $style }}">{{ $auxiliar->nombre_cuenta }}</td>
<td style="{{ $style }}">
    @if ($auxiliar->numero_documento)
        {{ $auxiliar->numero_documento }}
    @endif
</td>
<td style="{{ $style }}">
    @if ($auxiliar->razon_social)
        {{ $auxiliar->razon_social }}
    @else 
        {{ $auxiliar->nombre_nit }}
    @endif
</td>
<td style="{{ $style }}">
    @if ($auxiliar->codigo_cecos)
        {{ $auxiliar->codigo_cecos }} - {{ $auxiliar->nombre_cecos }}
    @endif
</td>
<td style="{{ $style }}">{{ $auxiliar->documento_referencia }}</td>
<td style="{{ $style }} text-align: right;">{{ number_format($auxiliar->saldo_anterior) }}</td>
<td style="{{ $style }} text-align: right;">{{ number_format($auxiliar->debito) }}</td>
<td style="{{ $style }} text-align: right;">{{ number_format($auxiliar->credito) }}</td>
<td style="{{ $style }} text-align: right;">{{ number_format($auxiliar->saldo_final) }}</td>
<td style="{{ $style }}">
    @if ($auxiliar->codigo_comprobante)
        {{ $auxiliar->codigo_comprobante }} - {{ $auxiliar->nombre_comprobante }}
    @endif
</td>
<td style="{{ $style }}">{{ $auxiliar->consecutivo }}</td>
<td style="{{ $style }}">{{ $auxiliar->fecha_manual }}</td>
<td style="{{ $style }}">{{ $auxiliar->concepto }}</td>