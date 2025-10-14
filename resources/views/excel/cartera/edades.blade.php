
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
            <th>Documento</th>
            <th>Nombre</th>
            <th>Ubicación</th>
            <th>Detalle</th>
            <th>De 0 a 30</th>
            <th>De 30 a 60</th>
            <th>De 60 a 90</th>
            <th>Más de 90</th>
            <th>Saldo final</th>
		</tr>
		</thead>
		<tbody>
		@foreach($documentos as $documento)
			<tr>
                @if ($documento->nivel == 1)
                    @include('excel.cartera.celdas_edades', ['style' => 'background-color: #212329; font-weight: bold; color: white;', 'documento' => $documento, 'cabeza' => $cabeza])
                @else
                    @include('excel.cartera.celdas_edades', ['style' => 'background-color: #FFF;', 'documento' => $documento, 'cabeza' => $cabeza])
                @endif
			</tr>
		@endforeach
		</tbody>
	</table>
</html>