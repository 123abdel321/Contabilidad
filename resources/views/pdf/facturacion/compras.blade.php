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
									<p> {{ $factura->comprobante->nombre }} <br>
										<span span class="numero-consecutivo">N° {{ $factura->consecutivo }}</span>
									</p>
								</td>
								<td class="empresa padding5">
									<h1>{{ $empresa->razon_social }}</h1>
									<span>NIT: {{ $empresa->nit }}-{{ $empresa->dv }}</span><br>
									<span>{{ $empresa->direccion }}</span><br>
									<span>TEL: {{ $empresa->telefono }}</span><br>
								</td>
								
								<td class="logo padding5">
									<img src="{{ $empresa->logo }}">
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</thead>
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
												<th colspan="2" class="header-total padding5">PROVEEDOR</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td class="padding3">{{ $proveedor->nombre_completo }}</td>
											</tr>
											<tr>
												<td class="padding3">{{ $proveedor->tipo_documento->nombre }} N° {{ $proveedor->numero_documento }}</td>
											</tr>
											<tr>
												<td class="padding3">{{ $proveedor->direccion }} {{ $proveedor->ciudad->nombre }}</td>
											</tr>
											<tr>
												<td class="padding3"> TEL: {{ $proveedor->telefono }}</td>
											</tr>
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
											<tr >
												<td class="padding5">Fecha</td>
												<td class="valor padding5">{{ $factura->fecha_manual }}</td>
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
					<th class="padding5">NOMBRE</th>
					<th class="padding5">CANTIDAD</th>
					<th class="padding5">COSTO</th>
					<th class="padding5">SUBTOTAL</th>
					<th class="padding5">DESCUENTO</th>
					<th class="padding5">IVA</th>
					<th class="padding5">TOTAL</th>
				</tr>
			</thead>
			<tbody class="detalle-factura">
				@foreach ($productos as $producto)
					<tr>
						<td class="padding5 detalle-factura-descripcion">{{ $producto->descripcion }}</td>
						<td class="padding5 valor">{{ number_format($producto->cantidad) }}</td>
						<td class="padding5 valor">{{ number_format($producto->producto) }}</td>
						<td class="padding5 valor">{{ number_format($producto->subtotal) }}</td>
						<td class="padding5 valor">{{ number_format($producto->descuento_valor) }}</td>
						<td class="padding5 valor">{{ number_format($producto->iva_valor) }}</td>
						<td class="padding5 valor">{{ number_format($producto->total) }}</td>
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
								</td>
								
								<td class="table-total-factura padding5">
									<table>
										<tbody>
											<tr >
												<td >FECHA</td>
												<td class="valor ">{{ $factura->fecha_manual }}</td>
											</tr>
                                            <tr>
                                                <td >SUBTOTAL</td>
                                                <td class="valor ">{{ number_format($factura->subtotal) }}</td>
                                            </tr>
											<tr>
                                                <td >IVA</td>
                                                <td class="valor ">{{ number_format($factura->total_iva) }}</td>
                                            </tr>
											<tr>
                                                <td >RETE FUENTE {{ $factura->porcentaje_rete_fuente }}%</td>
                                                <td class="valor ">{{ number_format($factura->total_rete_fuente) }}</td>
                                            </tr>
											<tr>
                                                <td style="font-weight: bold;">TOTAL</td>
                                                <td class="valor ">{{ number_format($factura->total_factura) }}</td>
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
				<td class="padding5 ">
					<table>
						<tr >
							<td class="empresa-footer padding5">
								LISTAR DATOS<br>
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