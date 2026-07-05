<!-- A --><td style="{{ $style }}">{{ $resultado->cuenta }}</td>
<!-- B --><td style="{{ $style }}">{{ $resultado->nombre_cuenta }}</td>
<!-- C --><td style="{{ $style }}">{{ $resultado->saldo_anterior }}</td>

<!-- Meses dinámicos -->
@foreach($mesesMostrar as $mes)
    <td style="{{ $style }}">{{ $resultado->{$mes} }}</td>
@endforeach

<!-- Saldo final y presupuestos -->
<td style="{{ $style }}">{{ $resultado->saldo_final }}</td>
<td style="{{ $style }}">{{ $resultado->ppto_anterior }}</td>
<td style="{{ $style }}">{{ $resultado->ppto_movimiento }}</td>
<td style="{{ $style }}">{{ $resultado->ppto_acumulado }}</td>
<td style="{{ $style }}">{{ $resultado->ppto_diferencia }}</td>
<td style="{{ $style }}">{{ $resultado->ppto_porcentaje }}</td>
<td style="{{ $style }}">{{ $resultado->ppto_porcentaje_acumulado }}</td>