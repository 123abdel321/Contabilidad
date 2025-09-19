<!-- A --><td style="{{ $style }}">
    @if ($balance->cuenta && $balance->auxiliar != 5)
        {{ $balance->cuenta }}
    @else
        {{ $balance->cuenta }} - {{ $balance->nombre_cuenta }}
    @endif
</td>
<!-- A --><td style="{{ $style }}">
    @if ($balance->cuenta && $balance->auxiliar != 5)
        {{ $balance->nombre_cuenta }}
    @endif
</td>

<!-- B --><td style="{{ $style }}">{{ $balance->saldo_anterior }}</td>
<!-- C --><td style="{{ $style }}">{{ $balance->debito }}</td>
<!-- D --><td style="{{ $style }}">{{ $balance->credito }}</td>
<!-- E --><td style="{{ $style }}">{{ $balance->saldo_final }}</td>