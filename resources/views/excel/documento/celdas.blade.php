<!-- A --><td style="{{ $style }}">
    @if ($documento->nivel == '99')
        TOTALES
    @elseif ($documento->id_cuenta)
        {{ $documento->cuenta }}
    @endif
</td>
<!-- B --><td style="{{ $style }}">
    @if ($documento->id_cuenta)
        {{ $documento->nombre_cuenta }}
    @endif
</td>
<!-- C --><td style="{{ $style }}">{{ $documento->numero_documento }}</td>
<!-- D --><td style="{{ $style }}">{{ $documento->nombre_nit ? $documento->nombre_nit : $documento->razon_social }}</td>
<!-- E --><td style="{{ $style }}">
    @if ($documento->codigo_comprobante)
        {{ $documento->codigo_comprobante }} - {{ $documento->nombre_comprobante }}
    @endif
</td>
<!-- F --><td style="{{ $style }}">{{ $documento->consecutivo }}</td>
<!-- G --><td style="{{ $style }}">
   @if ($documento->codigo_cecos)
       {{ $documento->codigo_cecos }} - {{ $documento->nombre_cecos }}
   @endif 
</td>
<!-- H --><td style="{{ $style }}">{{ $documento->documento_referencia }}</td>
<!-- I --><td style="{{ $style }} font-weight: bold;">{{ $documento->debito }}</td>
<!-- J --><td style="{{ $style }} font-weight: bold;">{{ $documento->credito }}</td>
<!-- K --><td style="{{ $style }} font-weight: bold;">{{ $documento->diferencia }}</td>
<!-- L --><td style="{{ $style }}">{{ $documento->fecha_manual }}</td>
<!-- M --><td style="{{ $style }}">{{ $documento->concepto }}</td>
<!-- N --><td style="{{ $style }} font-weight: bold;">{{ $documento->base_cuenta }}</td>
<!-- O --><td style="{{ $style }}">{{ $documento->porcentaje_cuenta }}</td>
<!-- P --><td style="{{ $style }}">{{ $documento->total_columnas }}</td>
