<html>

    <head>
        <meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title></title>

        <style>
			body {
                margin: 0;
                font-family: "Segoe UI", "Lato", Arial, sans-serif;
                line-height: 1.4;
                font-size: 11px;
                width: 100%;
                text-transform: uppercase;
                color: #2c3e50;
                background-color: #ffffff;
            }

			.text-title {
				font-size: 15px;
				font-weight: bold;
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

        <table>
            <thead>
				<tr>
					<td class="spacer padding5"></td>
				</tr>
				<tr>
					<td colspan="7 padding5">
						<table>
							<tr>
								<td class="consecutivo padding5">
									<h2> INFORME AUXILIAR </h2>
								</td>
								<td class="empresa padding5">
									<h1>{{ $empresa->razon_social }}</h1>
									<span class="text-title">NIT: 
										@if ($empresa->dv)
											{{ $empresa->nit }}-{{ $empresa->dv }}
										@else
											{{ $empresa->nit }}
										@endif
									</span><br>
									@if ($empresa->direccion)
										<span class="text-title">{{ $empresa->direccion }}</span><br>
									@endif
									@if ($empresa->telefono)
										<span class="text-title">TEL: {{ $empresa->telefono }}</span><br>
									@endif
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

        <table class="tabla-detalle-factura">
            <thead class="">
				<tr>
					<td class="spacer"></td>
				</tr>
				<tr class="header-factura padding5">
					<th class="padding5">CUENTA</th>
					<th class="padding5">NOMBRE</th>
					<th class="padding5">DOC. NIT</th>
					<th class="padding5">NOMBRE NIT</th>
					<th class="padding5">C. COSTOS</th>
					<th class="padding5">FACTURA</th>
					<th class="padding5">SAL. ANTERIOR</th>
					<th class="padding5">DEBITO</th>
					<th class="padding5">CREDITO</th>
					<th class="padding5">SAL. FINAL</th>
					<th class="padding5">COMPROB.</th>
					<th class="padding5">CONSEC</th>
					<th class="padding5">FECHA</th>
					<th class="padding5">CONCEP</th>
				</tr>
			</thead>
            <tbody class="">

				@foreach($auxiliares as $auxiliar)
					@php
						$style = 'background-color: #FFFFFF;'; // Default style
						
						// Validación de saldos para nits (primera prioridad)
						if($auxiliar->detalle_group == 'nits') {
							if(($auxiliar->naturaleza_cuenta == 0 && intval($auxiliar->saldo_final) < 0) || 
							($auxiliar->naturaleza_cuenta == 1 && intval($auxiliar->saldo_final) > 0)) {
								$cuenta = substr($auxiliar->cuenta, 0, 2);
								if ($cuenta != '11') {
									$style = 'background-color: #FF00004D; color: #000000;';
								}
							}
						}
						
						// Si tiene auxiliar, mantener estilo default
						elseif($auxiliar->auxiliar) {
							$style = 'background-color: #FFFFFF;';
						}
						
						// Resto de condiciones
						elseif($auxiliar->detalle_group == 'nits-totales') {
							$style = 'background-color: #9BD8E9; font-weight: 600;';
						}
						elseif($auxiliar->detalle_group == 'nits') {
							$style = 'background-color: #9BD8E9; font-weight: 600;';
						}
						elseif($auxiliar->cuenta == "TOTALES") {
							$style = 'background-color: #000000; font-weight: bold; color: #FFFFFF;';
						}
						elseif(strlen($auxiliar->cuenta) == 1) {
							$style = 'background-color: #212329; font-weight: bold; color: #FFFFFF;';
						}
						elseif(strlen($auxiliar->cuenta) == 2) {
							$style = 'background-color: #4D4F54; font-weight: bold; color: #FFFFFF;';
						}
						elseif(strlen($auxiliar->cuenta) == 4) {
							$style = 'background-color: #33849E; font-weight: 600; color: #FFFFFF;';
						}
						elseif(strlen($auxiliar->cuenta) == 6) {
							$style = 'background-color: #9BD8E9; font-weight: 600;';
						}
						elseif($auxiliar->detalle == 0 && $auxiliar->detalle_group == 0) {
							$style = 'background-color: #FFFFFF;';
						}
						elseif($auxiliar->detalle_group && !$auxiliar->detalle) {
							$style = 'background-color: #9BD8E9; font-weight: 600;';
						}
						elseif($auxiliar->detalle) {
							$style = 'background-color: #9BD8E9; font-weight: 600;';
						}
					@endphp
					
					<tr>
						@include('pdf.informes.auxiliar.celdas', ['style' => $style, 'auxiliar' => $auxiliar])
					</tr>
				@endforeach
            </tbody>
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