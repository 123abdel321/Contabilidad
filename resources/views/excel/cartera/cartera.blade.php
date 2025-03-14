
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table>
		<thead>
		<tr>
            <th>Cuenta</th>
            <th>Nit</th>
            <th>Zona</th>
            <th>Ubicación</th>
            <th>Saldo anterior</th>
            <th>Total factura</th>
            <th>Total abono</th>
            <th>Saldo final</th>
		</tr>
		</thead>
		<tbody>
		@foreach($documentos as $documento)
			<tr>
                @if ($documento->nivel == 1)
                    @include('excel.cartera.celdas', ['style' => 'background-color: #b3dbed; font-weight: bold;', 'documento' => $documento, 'cabeza' => $cabeza])
                @elseif ($documento->nivel == 0)
                    @include('excel.cartera.celdas', ['style' => 'background-color: #1c4587; font-weight: bold; color: #FFF;', 'documento' => $documento, 'cabeza' => $cabeza])
                @else
                    @include('excel.cartera.celdas', ['style' => 'background-color: #FFF;', 'documento' => $documento, 'cabeza' => $cabeza])
                @endif
			</tr>
		@endforeach
		</tbody>
	</table>
</html>