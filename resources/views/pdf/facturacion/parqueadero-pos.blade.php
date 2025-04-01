<html>

	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Facturas ventas POS</title>

        <style>
            body {
				margin: 0;
				font-family: "Lato", sans-serif;
				line-height: 16px;
				width: 100%;
				text-transform: uppercase;
			}

            .width-100 {
                width: 100%;
            }

            .spacer {
				height: 30px;
			}

            .spacer-10 {
                height: 10px;
            }

            .padding5 {
				padding: 5px;
			}

            .padding2 {
				padding: 2px;
			}

            .center-item {
                text-align: center;
            }

            .font-10 {
                font-size:9px;
            }

            .font-12 {
                font-size:12px;
            }

            .font-13 {
                font-size:13;
            }

            .font-15 {
                font-size:15;
            }

            .font-20 {
                font-size:25px;
            }

            .border-dashed {
                border-bottom: 1px solid #000;
                border-bottom-style: dashed;
            }
        </style>

    </head>

    <body>
    <!-- <body onload="window.print()"> -->
        <table class="width-100">
            <thead class="center-item">
                <tr>
					<td class="spacer padding5"></td>
				</tr>
                <tr>
                    <td style="width:100%;">
                        @if ($empresa->logo)
                            <img style="height:65px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}" />
                        @else
                            <img style="height:65px;" src="/img/logo_contabilidad.png" />
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>
                        <b class="font-13 center-item"> {{ $empresa->razon_social }} </b>
                    </th>
                </tr>
                <tr><th class="font-12 center-item">
                    {{ $empresa->nit }}-{{ $empresa->dv }}
                </th></tr>
                <tr><th class="font-12 center-item">
                    {{ $empresa->direccion }}
                </th></tr>
                <tr><th class="font-12 center-item">
                    TELEFONO: {{ $empresa->telefono }}
                </th></tr>

                <tr>
					<th class="spacer-10"></th>
				</tr>

                <tr>
                    <th class="border-dashed font-13" style="width:100%;"> <b class="font-15">VEHICULO</b></th>
                </tr>

                <tr>
                    <th class="font-20">
                        @if ($factura->producto->imagen)
                            <img style="height:65px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $factura->producto->imagen }}" />
                        @else
                            {{ $factura->tipo == 1 ? 'CARRO' : 'MOTO' }}
                        @endif
                        <!-- <img style="height:65px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}" /> -->
                        <br/>
                        {{ $factura->placa }}
                    </th>
                </tr>

                <tr>
					<th class="spacer-10"></th>
				</tr>
                
                <tr>
                    <th class="font-15">
                        FECHA ENTRADA: {{ $factura->fecha_inicio }}
                    </th>
                </tr>

                <tr>
                    <th class="font-15">
                        TARIFA: {{ $factura->producto->codigo }} - {{ $factura->producto->nombre }}
                    </th>
                </tr>

                <tr>
					<th class="spacer padding5"></th>
				</tr>
            </thead>
        </table>

        <table class="width-100">
            <thead class="center-item">
                <tr>
					<td class="spacer padding5"></td>
				</tr>
                <tr>
                    <td class="font-12">
                        USUARIO: {{ $usuario }}
                    </td>
                </tr>
                <tr>
                    <td class="font-12">
                        {{ $fecha_pdf }}
                    </td>
                </tr>
                <tr>
                    <td class="spacer-10 padding5"></td>
                </tr>

                <tr>
                    <td class="font-10">
                        Esta factura fue generado por Portafolio ERP
                    </td>
                </tr>
                <tr>
                    <td class="font-10">
                        www.portafolioerp.com | NIT: 103665647-7
                    </td>
                </tr>
            </thead>
        </table>

    </body>

</html>