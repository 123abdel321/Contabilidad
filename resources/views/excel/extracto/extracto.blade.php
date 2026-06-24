
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    @include('excel.base_header', ['encabezado' => $encabezado])

	<table>
		<thead>
		<tr>            
            <th>Cuenta</th>
            <th>Nombre</th>
            <th>Factura</th>
            <th>Saldo anterior</th>
            <th>Debito</th>
            <th>Credito</th>
            <th>Saldo final</th>
            <th>Comprobante</th>
            <th>Consec.</th>
            <th>Fecha</th>
            <th>Concepto</th>
		</tr>
		</thead>
		<tbody>
            @foreach($documentos as $documento)
                <tr>
                    @if ($documento->nivel == 1)
                        @include('excel.extracto.celdas', [
                            'style' => 'background-color: #00000000; font-weight: bold; color: white;',
                            'documento' => $documento
                        ])
                    @elseif ($documento->nivel == 2 && $documento->auxiliar == 0 && $documento->cuenta.length == 1)
                        @include('excel.extracto.celdas', [
                            'style' => 'background-color: #397edb; font-weight: 600;',
                            'documento' => $documento
                        ])
                    @elseif ($documento->nivel == 2 && $documento->auxiliar == 0 && $documento->cuenta.length == 2)
                        @include('excel.extracto.celdas', [
                            'style' => 'background-color: #6198e2; font-weight: 600;',
                            'documento' => $documento
                        ])
                    @elseif ($documento->nivel == 2 && $documento->auxiliar == 0 && $documento->cuenta.length == 4)
                        @include('excel.extracto.celdas', [
                            'style' => 'background-color: #88b2e9; font-weight: 600;',
                            'documento' => $documento
                        ])
                    @elseif ($documento->nivel == 2 && $documento->auxiliar == 0 && $documento->cuenta.length == 6)
                        @include('excel.extracto.celdas', [
                            'style' => 'background-color: #b0cbf1; font-weight: 600;',
                            'documento' => $documento
                        ])
                    @elseif ($documento->nivel == 2 && $documento->auxiliar == 1)
                        @include('excel.extracto.celdas', [
                            'style' => 'background-color: #d7e5f8; font-weight: 600;',
                            'documento' => $documento
                        ])
                    @elseif ($documento->nivel == 3)
                        @include('excel.extracto.celdas', [
                            'style' => 'background-color: #f1f1f1; font-weight: 600;',
                            'documento' => $documento
                        ])
                    @elseif ($documento->nivel == 5)
                        @include('excel.extracto.celdas', [
                            'style' => 'background-color: #00000000; font-weight: 600; color: white',
                            'documento' => $documento
                        ])
                    @elseif ($documento->nivel == 6)
                        @include('excel.extracto.celdas', [
                            'style' => 'border-Bottom: 1px solid #bababa;',
                            'documento' => $documento
                        ])
                    @endif
                </tr>
            @endforeach
		</tbody>
	</table>
</html>