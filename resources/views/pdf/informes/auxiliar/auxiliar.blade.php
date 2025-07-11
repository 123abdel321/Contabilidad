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
                @foreach ($auxiliares as $auxiliar)
					<tr>
						@if ($auxiliar->id_nit)
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliar])
						@elseif($auxiliar->detalle_group == 'nits-totales')
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #cfe8f3; font-weight: 600;', 'auxiliar' => $auxiliar])
						@elseif($auxiliar->detalle_group == 'nits')
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #e3f1f8;', 'auxiliar' => $auxiliar])
						@elseif($auxiliar->cuenta == 'TOTALES')
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #1c4587; font-weight: 600; color: white;', 'auxiliar' => $auxiliar])
						@elseif(strlen($auxiliar->cuenta) == 1)  
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #53add6; font-weight: 600;', 'auxiliar' => $auxiliar])
						@elseif(strlen($auxiliar->cuenta) == 2) 
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #70bbdd; font-weight: 600;', 'auxiliar' => $auxiliar])
						@elseif(strlen($auxiliar->cuenta) == 4) 
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #8cc8e3; font-weight: 600;', 'auxiliar' => $auxiliar])
						@elseif($auxiliar->detalle == 0 && $auxiliar->detalle_group == 0) 
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliar])
						@elseif($auxiliar->detalle_group && !$auxiliar->detalle)
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #a9d6ea; font-weight: 600;', 'auxiliar' => $auxiliar])
						@elseif($auxiliar->detalle)
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #bcdfef; font-weight: 600;', 'auxiliar' => $auxiliar])
						@else
							@include('pdf.informes.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliar])
						@endif
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