<!-- A --><td style="{{ $style }}">{{ $auxiliar->cuenta }} - {{ $auxiliar->nombre_cuenta }}</td>
<!-- B --><td style="{{ $style }}">
    @if (!$auxiliar->numero_documento)
       
    @elseif ($auxiliar->razon_social)
        {{ $auxiliar->numero_documento }} - {{ $auxiliar->razon_social }}
    @else 
        {{ $auxiliar->numero_documento }} - {{ $auxiliar->nombre_nit }}
    @endif
</td>
<!-- C --><td style="{{ $style }}">
    @if ($auxiliar->codigo_cecos)
        {{ $auxiliar->codigo_cecos }} - {{ $auxiliar->nombre_cecos }}
    @endif
</td>
<!-- D --><td style="{{ $style }}">{{ $auxiliar->documento_referencia }}</td>
<!-- E --><td style="{{ $style }} font-weight: bold;">{{ $auxiliar->saldo_anterior }}</td>
<!-- F --><td style="{{ $style }} font-weight: bold;">{{ $auxiliar->debito }}</td>
<!-- G --><td style="{{ $style }} font-weight: bold;">{{ $auxiliar->credito }}</td>
<!-- H --><td style="{{ $style }} font-weight: bold;">{{ $auxiliar->saldo_final }}</td>
<!-- I --><td style="{{ $style }}">
    @if ($auxiliar->codigo_comprobante)
        {{ $auxiliar->codigo_comprobante }} - {{ $auxiliar->nombre_comprobante }}
    @endif
</td>
<!-- J --><td style="{{ $style }}">{{ $auxiliar->consecutivo }}</td>
<!-- K --><td style="{{ $style }}">{{ $auxiliar->fecha_manual }}</td>
<!-- L --><td style="{{ $style }}">{{ $auxiliar->concepto }}</td>