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
				font-size: 15px;
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
				font-size: 15px;
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

			.padding5 {
				padding: 5px;
			}

			.padding3 {
				padding: 2px;
			}

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
				line-height: 3em;
			}

			.numero-consecutivo {
				color: #8d00ff;
				font-size: 2.8em;
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
				width: 40%;
			}

			.aling-top {
				vertical-align: top;
			}

		</style>

	</head>

	<body class="main">

		<table >
			<thead>
				<tr>
					<td class="spacer padding5"></td>
				</tr>
				<tr>
					<td colspan="7 padding5">
						<table>
							<tr>
								<td class="consecutivo padding5">
									<p> {{ $pago->comprobante->nombre }} <br>
										<span span class="numero-consecutivo">N° {{ $pago->consecutivo }}</span>
									</p>
								</td>
								<td class="empresa padding5">
									<h1>{{ $empresa->razon_social }}</h1>
									<span>NIT: {{ $empresa->nit }}-{{ $empresa->dv }}</span><br>
									<span>{{ $empresa->direccion }}</span><br>
									<span>TEL: {{ $empresa->telefono }}</span><br>
								</td>
								
								<td class="logo padding5">
									@if ($empresa->logo)
										<img stype="height:70px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}">
									@else
										<img style="height:70px;" src="img/logo_contabilidad.png">
									@endif
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</thead>
		</table>

		@if($nit)
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
												<td class="padding3">{{ $nit->nombre_nit }}</td>
											</tr>
											<tr>
												<td class="padding3">{{ $nit->tipo_documento }} N° {{ $nit->numero_documento }}</td>
											</tr>
											<tr>
												<td class="padding3">{{ $nit->direccion }}
													@if($nit->ciudad)
														{{ $nit->ciudad }}
													@endif
												</td>
											</tr>
											@if ($nit->telefono)
												<tr>
													<td class="padding3"> TEL: {{ $nit->telefono }}</td>
												</tr>
											@endif
										</tbody>
									</table>
								</td>
								
								<td class="table-total-factura padding5">
									<table>
										<thead>
											<tr>
												<th colspan="2" class="header-total padding5">PAGO</th>
											</tr>
										</thead>
										<tbody>
											<tr >
												<td class="padding5">Fecha</td>
												<td class="valor padding5">{{ $pago->fecha_manual }}</td>
											</tr>
											@if($pago->total_abono)
												<tr>
													<td class="padding5" style="font-weight: bold;">Total abono</td>
													<td class="valor padding5">{{ $pago->total_abono }}</td>
												</tr>
											@endif
											@if($pago->total_anticipo)
												<tr>
													<td class="padding5" style="font-weight: bold;">Total anticipo</td>
													<td class="valor padding5">{{ $pago->total_anticipo }}</td>
												</tr>
											@endif
										</tbody>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</thead>
		</table> 
		@endif
		

		<table class="tabla-detalle-factura">
			<thead class="">
				<tr>
					<td class="spacer"></td>
				</tr>
				<tr class="header-factura padding5">
					<th class="padding5">CUENTA</th>
					<th class="padding5">NOMBRE</th>
					<th class="padding5">FACTURA</th>
					<th class="padding5">VALOR</th>
					<th class="padding5">PAGO</th>
					<th class="padding5">ANTICIPO</th>
					<th class="padding5">SALDO</th>
				</tr>
			</thead>
			<tbody class="detalle-factura">
				@foreach ($detalles as $detalle)
					<tr>
						<td class="padding5 detalle-factura-descripcion">{{ $detalle->cuenta->cuenta }}</td>
						<td class="padding5 detalle-factura-descripcion">{{ $detalle->cuenta->nombre }}</td>
						<td class="padding5 detalle-factura-descripcion">{{ $detalle->documento_referencia }}</td>
						<td class="padding5 valor">{{ number_format($detalle->total_saldo) }}</td>
						<td class="padding5 valor">{{ number_format($detalle->total_abono) }}</td>
						<td class="padding5 valor">{{ number_format($detalle->total_anticipo) }}</td>
						<td class="padding5 valor">{{ number_format($detalle->nuevo_saldo) }}</td>
					</tr>
				@endforeach
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
									<table class="width-100">
										<thead>
											<tr>
												<th colspan="2" class="header-total padding5">PAGOS</th>
											</tr>
										</thead>
										<tbody>
											@if (count($pagos) > 0)
												@foreach ($pagos as $pago)
													<tr>
														<td class="font-13" style="width:55%;">{{ $pago->forma_pago->nombre }}</td>
														<td class="font-13" style="width:45%;text-align:right;">{{ number_format($pago->valor) }}</td>
													</tr>
												@endforeach
											@endif
											<tr>
												<td style="font-weight: bold;">TOTAL</td>
												<td class="font-13" style="width:45%;text-align:right;">{{ number_format($pagos->sum('valor')) }}</td>
											</tr>
											<tr>
												<td class="spacer-10 padding5"></td>
											</tr>
										</tbody>
									</table>
								</td>
								<td class="aling-top padding5 width-100">
								</td>
								<td class="padding5">
								</td>
								<td class="padding5">
								</td>
								<td class="padding5">
								</td>
								<td class="padding5">
								</td>
								<td class="padding5">
								</td>
								<td class="padding5">
								</td>
								<td class="padding5">
								</td>
								<td class="padding5">
								</td>
								<td class="padding5">
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
		
	</body>

</html>