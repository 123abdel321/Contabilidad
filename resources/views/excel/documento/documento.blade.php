
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
            <th>Comprobante</th>
            <th>Consecutivo</th>
            <th>Centro</th>
            <th>Factura</th>
            <th>Debito</th>
            <th>Credito</th>
            <th>Diferencia</th>
            <th>Fecha</th>
            <th>Concepto</th>
            <th>Base</th>
            <th>Porcentaje</th>
            <th>Registros</th>
		</tr>
		</thead>
		<tbody>
            @foreach($documentos as $documento)
                <tr>
                    @if ($documento->nivel == 99)
                        @include('excel.documento.celdas', ['style' => 'background-color: #1c4587; font-weight: bold; color: white;', 'documento' => $documento])
                    @elseif ($documento->nivel == 1)
                        @include('excel.documento.celdas', ['style' => 'background-color: #9fd1e8; font-weight: bold;', 'documento' => $documento])
                    @elseif ($documento->nivel == 2)
                        @include('excel.documento.celdas', ['style' => 'background-color: #bcdfef; font-weight: bold;', 'documento' => $documento])
                    @elseif ($documento->nivel == 3)
                        @include('excel.documento.celdas', ['style' => 'background-color: #d9edf6; font-weight: bold;', 'documento' => $documento])
                    @elseif ($documento->nivel == 4)
                        @include('excel.documento.celdas', ['style' => 'background-color: #f5fafd; font-weight: bold;', 'documento' => $documento])
                    @else
                        @include('excel.documento.celdas', ['style' => 'background-color: #FFF;', 'documento' => $documento])
                    @endif
                </tr>
            @endforeach
		</tbody>
	</table>
</html>