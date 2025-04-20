<!-- A --><td style="{{ $style }}">{{ $balance->cuenta }}</td>
<!-- A --><td style="{{ $style }}">{{ $balance->nombre_cuenta }}</td>
<!-- B --><td style="{{ $style }} text-align: right;">{{ number_format($balance->saldo_anterior, 2) }}</td>
<!-- C --><td style="{{ $style }} text-align: right;">{{ number_format($balance->debito, 2) }}</td>
<!-- D --><td style="{{ $style }} text-align: right;">{{ number_format($balance->credito, 2) }}</td>
<!-- E --><td style="{{ $style }} text-align: right;">{{ number_format($balance->saldo_final, 2) }}</td>