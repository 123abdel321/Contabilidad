<!-- A --><td style="{{ $style }} padding:4px; border:1px solid #ddd;">{{ $auxiliar->cuenta }} - {{ $auxiliar->nombre_cuenta }}</td>
<!-- B --><td style="{{ $style }} padding:4px; border:1px solid #ddd;">
    @if (!$auxiliar->numero_documento)
    @elseif ($auxiliar->razon_social)
        {{ $auxiliar->numero_documento }} - {{ $auxiliar->razon_social }}
    @else
        {{ $auxiliar->numero_documento }} - {{ $auxiliar->nombre_nit }}
    @endif
</td>
<!-- C --><td style="{{ $style }} padding:4px; border:1px solid #ddd;">
    @if ($auxiliar->codigo_cecos){{ $auxiliar->codigo_cecos }} - {{ $auxiliar->nombre_cecos }}@endif
</td>
<!-- D --><td style="{{ $style }} padding:4px; border:1px solid #ddd;">{{ $auxiliar->documento_referencia }}</td>
<!-- E --><td style="{{ $style }} padding:4px; border:1px solid #ddd; font-weight:bold;">{{ $auxiliar->saldo_anterior }}</td>
<!-- F --><td style="{{ $style }} padding:4px; border:1px solid #ddd; font-weight:bold;">{{ $auxiliar->debito }}</td>
<!-- G --><td style="{{ $style }} padding:4px; border:1px solid #ddd; font-weight:bold;">{{ $auxiliar->credito }}</td>
<!-- H --><td style="{{ $style }} padding:4px; border:1px solid #ddd; font-weight:bold;">{{ $auxiliar->saldo_final }}</td>
<!-- I --><td style="{{ $style }} padding:4px; border:1px solid #ddd;">
    @if ($auxiliar->codigo_comprobante){{ $auxiliar->codigo_comprobante }} - {{ $auxiliar->nombre_comprobante }}@endif
</td>
<!-- J --><td style="{{ $style }} padding:4px; border:1px solid #ddd;">{{ $auxiliar->consecutivo }}</td>
<!-- K --><td style="{{ $style }} padding:4px; border:1px solid #ddd;">
    {{ $auxiliar->fecha_manual }}
</td>
<!-- L --><td style="{{ $style }} padding:4px; border:1px solid #ddd;">{{ $auxiliar->concepto }}</td>