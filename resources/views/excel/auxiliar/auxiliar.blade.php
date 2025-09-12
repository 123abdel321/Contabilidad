
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
                <th>Nit</th>
                <th>Centro costos</th>
                <th>Dcto refe</th>
                <th>Saldo anterior</th>
                <th>Debito</th>
                <th>Credito</th>
                <th>Saldo final</th>
                <th>Comprobante</th>
                <th>Consecutivo</th>
                <th>Fecha</th>
                <th>Concepto</th>
            </tr>
		</thead>
		<tbody>
		@foreach($auxiliares as $auxiliar)
			<tr>
                @if($auxiliar->detalle_group == 'nits-totales')
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #9bd8e9ff; font-weight: 600;', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->detalle_group == 'nits')
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #9bd8e9ff; font-weight: 600;', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->cuenta == 'TOTALES')
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #000; font-weight: bold; color: white;', 'auxiliar' => $auxiliar])
                @elseif(strlen($auxiliar->cuenta) == 1)  
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #212329; font-weight: bold; color: white;', 'auxiliar' => $auxiliar])
                @elseif(strlen($auxiliar->cuenta) == 2) 
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #4d4f54; font-weight: bold; color: white;', 'auxiliar' => $auxiliar])
                @elseif(strlen($auxiliar->cuenta) == 4) 
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #33849e; font-weight: 600; color: white;', 'auxiliar' => $auxiliar])
                @elseif(strlen($auxiliar->cuenta) == 6) 
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #9bd8e9ff; font-weight: 600;', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->detalle == 0 && $auxiliar->detalle_group == 0) 
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->detalle_group && !$auxiliar->detalle)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #9bd8e9ff; font-weight: 600;', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->detalle)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #9bd8e9ff; font-weight: 600;', 'auxiliar' => $auxiliar])
                @else
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliar])
                @endif
			</tr>
		@endforeach
		</tbody>
	</table>
</html>