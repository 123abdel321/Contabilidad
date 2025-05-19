
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table>
		<thead>
		<tr>
            @for ($i = 0; $i < count($cuentas); $i++)
                <th>{{ $cuentas[$i] }}</th>
            @endfor
		</tr>
		</thead>
		<tbody>
            @foreach ($detalles as $detalle)
                <tr>
                    {{-- Campos fijos --}}
                    <td>{{ $detalle->numero_documento }}</td>
                    <td>{{ $detalle->nombre_nit }}</td>
                    <td>{{ $detalle->ubicacion }}</td>

                    {{-- Campos dinámicos: cuenta_1, cuenta_2, ... --}}
                    @for ($i = 1; $i <= (count($cuentas) - 5); $i++) {{-- -3 fijos, -2 finales --}}
                        <td style="text-align: right;">{{ number_format($detalle->{'cuenta_' . $i}) ?? 0 }}</td>
                    @endfor

                    {{-- Campos finales --}}
                    <td style="text-align: right;">{{ number_format($detalle->saldo_final) }}</td>
                    <td>{{ $detalle->dias_mora }}</td>
                </tr>
            @endforeach
		</tbody>
	</table>
</html>