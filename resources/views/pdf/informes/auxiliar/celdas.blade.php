@if ( !$filter->id_cuenta )
    <td>{{ $style }} - {{ $auxiliar->cuenta }}</td>
    <td>{{ $auxiliar->nombre_cuenta }}</td>
@endif
@if ( !$filter->id_nit )
    <td>
        @if ($auxiliar->numero_documento)
            {{ $auxiliar->numero_documento }}
        @endif
    </td>
    <td>
        @if ($auxiliar->razon_social)
            {{ $auxiliar->razon_social }}
        @else 
            {{ $auxiliar->nombre_nit }}
        @endif
    </td>
@endif
<td>
    @if ($auxiliar->codigo_cecos)
        {{ $auxiliar->codigo_cecos }} - {{ $auxiliar->nombre_cecos }}
    @endif
</td>
<td>{{ $auxiliar->documento_referencia }}</td>
<td class="text-right">{{ number_format($auxiliar->saldo_anterior) }}</td>
<td class="text-right">{{ number_format($auxiliar->debito) }}</td>
<td class="text-right">{{ number_format($auxiliar->credito) }}</td>
<td class="text-right">{{ number_format($auxiliar->saldo_final) }}</td>
<td>
    @if ($auxiliar->codigo_comprobante)
        {{ $auxiliar->codigo_comprobante }} - {{ $auxiliar->nombre_comprobante }}
    @endif
</td>
<td>{{ $auxiliar->consecutivo }}</td>
<td>{{ $auxiliar->fecha_manual }}</td>
<td>{{ $auxiliar->concepto }}</td>