<html>

	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Retención</title>

		<style>
			body {
				margin: 0;
				font-family: "Lato", sans-serif;
				line-height: 15px;
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
				height: 10px;
			}

			.valor {
				text-align: right;
			}

			table {
				width: 100%;
				border-collapse: collapse;
			}

			.table-detail {
				font-size: 10px;
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

			/* .padding5 {
				padding: 5px;
			}

			.padding3 {
				padding: 2px;
			} */

			.logo {
				width: 25%;
				text-align: center;
				vertical-align: middle;
				margin: 0px auto;
			}

			.logo img {
				height: 90px;
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
                font-size: 15px;
                font-weight: bold;
                line-height: 1.5em;
			}

			.numero-consecutivo {
				color: #8d00ff;
				font-size: 2.8em;
			}
			
			.generado {
				width: 40%;
			}

			.footer {
				position: absolute;
				bottom: 35px;
				line-height: 15px;
				font-size: 8px;
			}

			.header-total {
				border-bottom: 1px solid #ddd;
			}

			.table-total-factura {
				vertical-align: top;
				width: 40%;
			}

			.aling-top {
				vertical-align: top;
			}

			.break-word {
				word-break: break-all;
			}

			.no-transform {
				text-transform: none;
			}

		</style>

	</head>

	<body class="main">

		<table >
			<thead>
				<tr>
					<td colspan="7 padding5">
						<table>
							<tr>
                                <td class="consecutivo padding5">
									<p> CERTIFICADO DE RETENCION EN LA FUENTE
									</p>
								</td>
								<td class="empresa padding5">
									<h1>{{ $empresa->razon_social }}</h1>
									<span>NIT: {{ $empresa->nit }}-{{ $empresa->dv }}</span><br>
									@if ($empresa->direccion)
										<span>{{ $empresa->direccion }}</span><br>
									@endif
									@if ($empresa->telefono)
										<span>TEL: {{ $empresa->telefono }}</span><br>
									@endif
								</td>
								
								<td class="logo padding5">
									@if ($empresa->logo)
										<img style="height:90px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}">
									@else
										<img style="height:90px;" src="img/logo_contabilidad.png" />
									@endif
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</thead>
		</table>

        <table class="tabla-detalle-factura">
            <tbody class="">
                <tr>
					<td class="spacer padding5"></td>
				</tr>
                <tr>
                    <td class="empresa-footer">Con el fin de dar cumplimiento a las disposiciones legales vigentes sobre Retención en la Fuente, certificamos que entre {{ $filtros['fecha_desde'] }} y {{ $filtros['fecha_hasta'] }} practicamos la retención en la fuente que se detalla en cada uno de los conceptos y valores.
                    </td>
                </tr>
                <tr>
					<td class="spacer padding5"></td>
				</tr>
            </tbody>
        </table>

		<table>
			<thead class="">
				<tr>
					<td class="spacer padding5"></td>
				</tr>
				<tr>
					<td colspan="8 padding5">
						<table>
							<tr>
								<td class="aling-top padding5">
									<table>
										<thead>
											<tr>
												<th colspan="2" class="header-total padding5">CLIENTE</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="padding3">{{ $cliente->nombre_completo }}</td>
											</tr>
											<tr>
												<td class="padding3">{{ $cliente->tipo_documento->nombre }} N° {{ $cliente->numero_documento }}</td>
											</tr>
											<tr>
												<td class="padding3">
													{{ $cliente->direccion }}
													@if($cliente->ciudad)
														{{ $cliente->ciudad->nombre }}
													@endif
												</td>
											</tr>
											@if ($cliente->telefono)
											<tr>
												<td class="padding3"> TEL: {{ $cliente->telefono }}</td>
											</tr>
											@endif
										</tbody>
									</table>
								</td>

                                <td class="table-total-factura padding5">
									<table>
										<thead>
											<tr>
												<th colspan="2" class="header-total padding5">FACTURA</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="padding5">Fecha desde</td>
												<td class="valor padding5">{{ $filtros['fecha_desde'] }}</td>
											</tr>
											<tr>
												<td class="padding5">Fecha hasta</td>
												<td class="valor padding5">{{ $filtros['fecha_hasta'] }}</td>
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

		<table class="tabla-detalle-factura">
			<thead class="">
				<tr>
					<td class="spacer"></td>
				</tr>
				<tr class="header-factura padding5">
					<th class="padding5">CUENTA</th>
					<th class="padding5">NOMBRE</th>
					<th class="padding5">SALDO ANTERIOR</th>
					<th class="padding5">DEBITO</th>
					<th class="padding5">CREDITO</th>
					<th class="padding5">VALOR BASE</th>
					<th class="padding5">PORCENTAJE</th>
					<th class="padding5">SALDO FINAL</th>
				</tr>
			</thead>
			<tbody class="detalle-factura">
				@foreach ($detalles as $detalle)
					<tr>
						<td class="padding5">{{ $detalle->cuenta }}</td>
						<td class="padding5">{{ $detalle->nombre_cuenta }}</td>
						<td class="padding5 valor">{{ number_format($detalle->saldo_anterior, 2) }}</td>
						<td class="padding5 valor">{{ number_format($detalle->debito, 2) }}</td>
						<td class="padding5 valor">{{ number_format($detalle->credito, 2) }}</td>
						<td class="padding5 valor">{{ number_format($detalle->valor_base, 2) }}</td>
						<td class="padding5 valor">{{ number_format($detalle->porcentaje_base, 2) }}</td>
						<td class="padding5 valor">{{ number_format($detalle->saldo, 2) }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>

		<table class="footer">
			<tr>
				<td class="padding5 ">
					<table>
						<tr >
							<td class="empresa-footer padding5">
								Portafolio ERP<br>
								{{ $fecha_pdf }}
							</td>
						</tr>
					</table>
				</td>
				<td class="padding5"></td>
				<td class="padding5 generado">
					<table>
						<tr>
							<td class="empresa-footer-left padding5">
								ESTE INFORME FU&Eacute; GENERADO POR PORTAFOLIOERP <br>
								www.portafolioerp.com
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table> 

		<script type="text/php">
			if ( isset($pdf) ) {
				$pdf->page_script('
					$font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
					$pdf->text(300, 800, "$PAGE_NUM / $PAGE_COUNT", $font, 8);
				');
			}
		</script>
		
	</body>

</html>