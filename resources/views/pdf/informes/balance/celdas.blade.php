<!-- A --><td style="{{ $style }}">{{ $balance->cuenta }}</td>
<!-- A --><td style="{{ $style }}">{{ $balance->nombre_cuenta }}</td>
<!-- B --><td style="{{ $style }} text-align: end;">{{ number_format($balance->saldo_anterior) }}</td>
<!-- C --><td style="{{ $style }} text-align: end;">{{ number_format($balance->debito) }}</td>
<!-- D --><td style="{{ $style }} text-align: end;">{{ number_format($balance->credito) }}</td>
<!-- E --><td style="{{ $style }} text-align: end;">{{ number_format($balance->saldo_final) }}</td>