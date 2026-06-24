<!-- A --><td style="{{ $style }}">{{ $documento->cuenta }}</td>
<!-- B --><td style="{{ $style }}">{{ $documento->nombre_cuenta }}</td>
<!-- C --><td style="{{ $style }}">{{ $documento->documento_referencia }}</td>
<!-- D --><td style="{{ $style }} font-weight: bold;">{{ $documento->saldo_anterior }}</td>
<!-- E --><td style="{{ $style }} font-weight: bold;">{{ $documento->debito }}</td>
<!-- F --><td style="{{ $style }} font-weight: bold;">{{ $documento->credito }}</td>
<!-- G --><td style="{{ $style }} font-weight: bold;">{{ $documento->saldo_final }}</td>
<!-- H --><td style="{{ $style }}">
    @if($documento->codigo_comprobante)
        {{ $documento->codigo_comprobante }} - {{ $documento->nombre_comprobante }}
    @endif
</td>
<!-- I --><td style="{{ $style }}">{{ $documento->consecutivo }}</td>
<!-- J --><td style="{{ $style }}">{{ $documento->fecha_manual }}</td>
<!-- K --><td style="{{ $style }}">{{ $documento->concepto }}</td>