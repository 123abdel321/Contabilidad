
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    @include('excel.base_header', ['encabezado' => $encabezado])

	<table>
		<thead>
		<tr>            
            <th>Cuenta</th>
            <th>Nombre</th>
            @if($tipo_informe == 'reteica')
                <th>Actividad economica</th>
            @endif
            <!-- <th>Saldo anterior</th> -->
            <th>Debito</th>
            <th>Credito</th>
            <th>Valor base</th>
            <th>Porcentaje</th>
            <!-- <th>Saldo final</th> -->
            @if($nivel == 3)
                <th>Fecha</th>
                <th>Consecutivo</th>
                <th>Comprobante</th>
                <th>Concepto</th>
            @endif
		</tr>
		</thead>
		<tbody>
            @foreach($documentos as $documento)
                <tr>

                    @if($nivel == 1)
                        @if($documento->nivel == 0)
                            @include('excel.impuesto.celdas', [
                                'style' => 'background-color: #001c41; font-weight: bold; color: white;',
                                'documento' => $documento,
                                'nivel' => $nivel,
                                'agrupado' => $agrupado 
                            ])
                        @elseif($documento->errores)
                            @include('excel.impuesto.celdas', [
                                'style' => 'background-color: #ff0000; font-weight: bold; color: white;',
                                'documento' => $documento,
                                'nivel' => $nivel,
                                'agrupado' => $agrupado 
                            ])
                        @endif
                    @elseif($nivel == 2)
                        @if($documento->nivel == 0)
                            @include('excel.impuesto.celdas', [
                                'style' => 'background-color: #1c4587; font-weight: bold; color: white;',
                                'documento' => $documento,
                                'nivel' => $nivel,
                                'agrupado' => $agrupado 
                            ])
                        @elseif($documento->nivel == 1)
                            @if($documento->errores)
                                @include('excel.impuesto.celdas', [
                                    'style' => 'background-color: #ff4141; font-weight: bold; color: white;',
                                    'documento' => $documento,
                                    'nivel' => $nivel,
                                    'agrupado' => $agrupado 
                                ])
                            @else
                                @include('excel.impuesto.celdas', [
                                    'style' => 'background-color: #b3dbed; font-weight: bold;',
                                    'documento' => $documento,
                                    'nivel' => $nivel,
                                    'agrupado' => $agrupado 
                                ])
                            @endif
                        @else
                            @include('excel.impuesto.celdas', [
                                'style' => '',
                                'documento' => $documento,
                                'nivel' => $nivel,
                                'agrupado' => $agrupado 
                            ])
                        @endif
                    @elseif($nivel == 3)
                        @if($documento->nivel == 0)
                            @include('excel.impuesto.celdas', [
                                'style' => 'background-color: #1c4587; font-weight: bold; color: white;',
                                'documento' => $documento,
                                'nivel' => $nivel,
                                'agrupado' => $agrupado 
                            ])
                        @elseif($documento->nivel == 1)
                            @include('excel.impuesto.celdas', [
                                'style' => 'background-color: #79bfdf; font-weight: bold;',
                                'documento' => $documento,
                                'nivel' => $nivel,
                                'agrupado' => $agrupado 
                            ])
                        @elseif($documento->nivel == 2)
                            @include('excel.impuesto.celdas', [
                                'style' => 'background-color: #d9edf6; font-weight: bold;',
                                'documento' => $documento,
                                'nivel' => $nivel,
                                'agrupado' => $agrupado 
                            ])
                        @endif
                    @else
                        @include('excel.impuesto.celdas', [
                            'style' => '',
                            'documento' => $documento,
                            'nivel' => $nivel,
                            'agrupado' => $agrupado 
                        ])
                    @endif

                </tr>
            @endforeach
		</tbody>
	</table>
</html>