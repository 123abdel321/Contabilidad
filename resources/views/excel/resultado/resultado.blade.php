
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <table>
        <tr>
            <td rowspan="3" style="vertical-align: middle; align-items: center; text-align: center;">
                <img src="{{ $logo_empresa }}" width="100" />
            </td>
            <td style="font-size: 25px; font-weight: bold;">{{ $nombre_empresa }}</td>
        </tr>
        <tr>
            <td style="font-size: 20px; font-weight: bold;">{{ $nombre_informe }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;">Fecha de generación: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

	<table>
		<thead>
            <tr>
                <th>Cuenta</th>
                <th>Nombre Cuenta</th>
                <th>Saldo anterior</th>
                <th>Debito</th>
                <th>Credito</th>
                <th>Saldo final</th>
                <th>Ppto anterior</th>
                <th>Pptp movimiento</th>
                <th>Ppto acumulado</th>
                <th>Ppto diferencia</th>
                <th>Ppto porcentaje</th>
                <th>Ppto porcentaje acumulado</th>
            </tr>
		</thead>
		<tbody>
		@foreach($resultados as $resultado)
            <tr>
                @if($resultado->cuenta == 'TOTALES')
                    @include('excel.resultado.celdas', ['style' => 'background-color: #000; font-weight: bold; color: #ffffff;', 'resultado' => $resultado])
                @elseif($resultado->auxiliar)
                    @include('excel.resultado.celdas', ['style' => 'background-color: #FFF; color: #000;', 'resultado' => $resultado])
                @elseif(strlen($resultado->cuenta) == 1 )
                    @include('excel.resultado.celdas', ['style' => 'background-color: #212329; color: #ffffff; font-weight: bold;', 'resultado' => $resultado])
                @elseif(strlen($resultado->cuenta) == 2 )
                    @include('excel.resultado.celdas', ['style' => 'background-color: #4d4f54; color: #ffffff; font-weight: bold;', 'resultado' => $resultado])
                @elseif(strlen($resultado->cuenta) == 4 )
                    @include('excel.resultado.celdas', ['style' => 'background-color: #33849e; color: #ffffff; font-weight: 600;', 'resultado' => $resultado])
                @elseif(strlen($resultado->cuenta) == 6 )
                    @include('excel.resultado.celdas', ['style' => 'background-color: #9bd8e9ff; font-weight: 600;', 'resultado' => $resultado])
                @else  
                    @include('excel.resultado.celdas', ['style' => 'background-color: #FFF; color: #000;', 'resultado' => $resultado])
                @endif
            </tr>
		@endforeach
		</tbody>
	</table>
</html>