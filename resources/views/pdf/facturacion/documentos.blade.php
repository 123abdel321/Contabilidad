<html>

	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title></title>

		<style>
			body {
				margin: 0;
				font-family: "Lato", sans-serif;
				line-height: 16px;
				font-size: 10px;
				width: 100%;
				text-transform: uppercase;
			}

			.detalle-factura td {
				border-left: 1px solid #ddd;
				border-right: 1px solid #ddd;
				vertical-align: bottom;
			}

			.detalle-factura>tr:last-child {
				border-bottom: 1px solid #ddd;
				height: 100%;
			}


			.spacer {
				height: 30px;
			}

			.valor {
				text-align: right;
			}

			table {
				width: 100%;
				border-collapse: collapse;
			}

			.table-detail {
				font-size: 12px;
				width: 100%;
				border-collapse: collapse;
				height: 100%;
			}

			.header-factura > th {
				border: 1px solid #ddd;
				background-color: #58978423;
			}

			thead {
				display: table-header-group
			}

			tr {
				page-break-inside: avoid
			}

			th, td {
				padding: 5px;
			}

			.logo {
				width: 25%;
				text-align: center;
				vertical-align: middle;
				margin: 0px auto;
			}

			.logo img {
				height: 100px;
			}

			.empresa {
				text-align: center;
				width: 50%;
			}

			.empresa-footer {
				text-align: center;
			}

			.empresa-footer-left {
				text-align: center;
				
			}

			.consecutivo {
				width: 25%;
				text-align: center;
				border: 1px solid #f2f2f2;
				line-height: 2em;
			}

			.numero-consecutivo {
				color: red;
				font-size: 2em;
			}
			
			.generado {
				width: 40%;
			}

			.footer {
				position: fixed;
				bottom: 35px;
				line-height: 15px;
				/* font-family: helvetica,arial,verdana,sans-serif; */
				font-size: 8px;
			}

			.header-total {
				border-bottom: 1px solid #ddd;
			}

			.table-total-factura {
				vertical-align: top;
			}

		</style>

	</head>

	<body class="main">

		<table >
			<thead>
				<tr>
					<td class="spacer"></td>
				</tr>
				<tr>
					<td colspan="7">
						<table>
							<tr>
								<td class="consecutivo">
									<p> {{ $factura->comprobante->nombre }} <br>
										<span span class="numero-consecutivo">N° {{ $factura->consecutivo }}</span>
									</p>
								</td>
								<td class="empresa">
									<h1>{{ $empresa->razon_social }}</h1>
									<span>NIT: {{ $empresa->nit }}-{{ $empresa->dv }}</span><br>
									<span>{{ $empresa->direccion }}</span><br>
									<span>TEL: {{ $empresa->telefono }}</span><br>
								</td>
								
								<td class="logo">
									<img src="{{ $empresa->logo }}">
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</thead>
		</table>

		<table class="tabla-detalle-factura">
			<thead class="">
				<tr>
					<td class="spacer"></td>
				</tr>
				<tr class="header-factura">
					<th>CUENTA</th>
					<th>NOMBRE</th>
					<th>FACTURA</th>
					<th>C. COSTOS</th>
					<th>DEBITO</th>
					<th>CREDITO</th>
					<th>SALDO</th>
				</tr>
			</thead>
			<tbody class="detalle-factura">
				@foreach ($documentos as $documento)
					<tr>
						<td class="detalle-factura-descripcion">{{ $documento->cuenta->cuenta }}</td>
						<td class="detalle-factura-descripcion">{{ $documento->cuenta->nombre }}</td>
						<td class="detalle-factura-descripcion">{{ $documento->documento_referencia }}</td>
						<td class="detalle-factura-descripcion">{{ $documento->centro_costos ? $documento->centro_costos->codigo : '' }} {{ $documento->centro_costos ? '-' : '' }} {{ $documento->centro_costos ? $documento->centro_costos->nombre : '' }}</td>
						<td class="valor">{{ number_format($documento->debito) }}</td>
						<td class="valor">{{ number_format($documento->credito) }}</td>
						<td class="valor">{{ number_format($documento->saldo) }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		<table>
			<thead class="">
				<tr>
					<td class="spacer"></td>
				</tr>
				<tr>
					<td colspan="7">
						<table>
							<tr>
								<td class="">
									<p>
										<b>Fecha factura:</b> <br>
										{{ $documento->fecha_manual }}
									</p>
									<p>
										<b>Observación:</b> <br>
										{{ $observacion }}
									</p>
								</td>
								
								<td class="table-total-factura">
									<table>
										<thead>
											<tr>
												<th colspan="2" class="header-total">Total</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td>Valor factura</td>
												<td class="valor">{{ $factura->saldo_final }}</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</thead>
		</table>
				
		<script type="text/php">
			if ( isset($pdf) ) {
				$pdf->page_script('
					$font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
					$pdf->text(300, 800, "$PAGE_NUM / $PAGE_COUNT", $font, 8);
				');
			}
		</script>

		<table class="footer">
			<tr>
				<td class="empresa-footer">
					<p>
						LISTAR DATOS<br>
						{{ $fecha_pdf }}
					</p>
				</td>
				<td class=""></td>
				<td class="generado">
					<table>
						<tr>
							<td class="empresa-footer-left">
								ESTE INFORME FU&Eacute; GENERADO POR LISTARDATOS <br>
								www.listardatos.com
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table> 
		
	</body>

</html>