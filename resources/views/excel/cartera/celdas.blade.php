<!-- A --><td style="{{ $style }}">
    @if ($documento->nivel == 2)
        {{ $documento->cuenta }}
    @else
        {{ $documento->numero_documento }}
    @endif
</td>
<!-- A --><td style="{{ $style }}">
    @if ($documento->nivel == 2)
        {{ $documento->nombre_cuenta }}
    @else
        {{ $documento->nombre_nit }}
    @endif
</td>
<!-- E --><td style="{{ $style }}">{{ $documento->saldo_anterior }}</td>
<!-- F --><td style="{{ $style }}">{{ $documento->total_facturas }}</td>
<!-- G --><td style="{{ $style }}">{{ $documento->total_abono }}</td>
<!-- H --><td style="{{ $style }}">{{ $documento->saldo }}</td>