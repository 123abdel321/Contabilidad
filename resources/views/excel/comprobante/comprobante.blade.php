
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table>
		<thead>
		<tr>            
            <th>Cuenta</th>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Nit</th>
            <th>Ubicación</th>
            <th>Documento</th>
            <th>Fecha</th>
            <th>Debito</th>
            <th>Credito</th>
            <th>Diferencia</th>
            <th>Concepto</th>
            <th>Registros</th>
		</tr>
		</thead>
		<tbody>
            @foreach($documentos as $documento)
                <tr>
                    @if ($documento->nivel == 4)
                        @include('excel.comprobante.celdas', ['style' => 'background-color: #1c4587; font-weight: bold; color: white;', 'documento' => $documento])
                    @elseif ($documento->nivel == 3)
                        @include('excel.comprobante.celdas', ['style' => 'background-color: #79bfdf; font-weight: 600;', 'documento' => $documento])
                    @elseif ($documento->nivel == 2)
                        @include('excel.comprobante.celdas', ['style' => 'background-color: #b3dbed; font-weight: 600;', 'documento' => $documento])
                    @elseif ($documento->nivel == 1)
                        @include('excel.comprobante.celdas', ['style' => 'background-color: #d9edf6; font-weight: 600;', 'documento' => $documento])
                    @else
                        @include('excel.comprobante.celdas', ['style' => 'background-color: #FFF;', 'documento' => $documento])
                    @endif
                </tr>
            @endforeach
		</tbody>
	</table>
</html>