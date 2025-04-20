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
                
                <tr><th class="font-12">
                    FECHA: {{ $pedido->updated_at }}
                </th></tr>

                <tr>
					<th class="spacer-10"></th>
				</tr>

                @if ($ubicacion)
                <tr>
                    <th class="font-13">
                        <b class="font-13 center-item">UBICACION: {{ $ubicacion->codigo }} - {{ $ubicacion->nombre }}</b>
                    </th>
                </tr>
                @endif

                <tr>
                    <th class="font-13">
                        <b class="font-13 center-item">PEDIDO #{{ $pedido->consecutivo }}</b>
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
                    <th class="border-dashed"  style="{{ $has_observacion ? 'width:35%' : 'width:55%' }};"> <b class="font-13">PRODUCTO </b></th>
                    <th class="border-dashed" style="width:5%; text-align:center;"> <b class="font-13">CANT. </b></th>
                    <th class="border-dashed" style="width:20%; text-align:center;"> <b class="font-13">V. UNI. </b></th>
                    @if ($pedido->total_descuento)
                        <th class="border-dashed" style="width:20%; text-align:center;"> <b class="font-13">DESC. </b></th>
                    @endif
                    @if ($has_observacion)
                        <th class="border-dashed" style="width:20%; text-align:center;"> <b class="font-13">Observación </b></th>
                    @endif
                    <th class="border-dashed" style="width:20%; text-align:right;"> <b class="font-13">TOTAL </b></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($productos as $producto)
                    <tr>
                        <td class="font-13" style="{{ $has_observacion ? 'width:35%' : 'width:55%' }};">
                            {{ $producto->descripcion }}
                        </td>
                        <td class="font-13" style="width:5%;text-align:center;">{{ number_format($producto->cantidad) }}</td>
                        <td class="font-13" style="width:20%;text-align:center;">{{ number_format($producto->costo) }}</td>
                        @if ($pedido->total_descuento)
                            <td class="font-13" style="width:auto;text-align:center;">{{ number_format($producto->descuento_valor) }}</td>
                        @endif
                        @if ($has_observacion)
                            <td class="font-13" style="width:20%; text-align:center;">{{ $producto->observacion }}</td>
                        @endif
                        <td class="font-13" style="width:20%;text-align:right;">{{ number_format($producto->total) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="spacer-10 padding5"></td>
                </tr>
            </tbody>
        </table>

        <table class="width-100">
            <thead class="center-item">
                <tr>
                    <th class="border-dashed font-13" style="width:100%;"> <b class="font-13">TOTALES DE FACTURA</b></th>
                </tr>
            </thead>
        </table>
        <table class="width-100">
            <tbody>
                <tr>
                    <td class="font-13" style="width:56%;">DESCRIPCION</td>
                    <td class="font-13" style="width:22%;">BASE</td>
                    <td class="font-13" style="width:22%;text-align:right;">TOTAL</td>
                </tr>
                <tr>
                    <td class="font-13" style="width:56%;">SUBTOTAL</td>
                    <td class="font-13" style="width:22%;"></td>
                    <td class="font-13" style="width:22%;text-align:right;">{{ number_format($pedido->subtotal) }}</td>
                </tr>
                @if ($pedido->total_descuento > 0)
                    <tr>
                        <td class="font-13" style="width:56%;">DESCUENTO</td>
                        <td class="font-13" style="width:22%;"></td>
                        <td class="font-13" style="width:22%;text-align:right;">{{ number_format($pedido->total_descuento) }}</td>
                    </tr>
                @endif

                @if ($pedido->total_rete_fuente > 0)
                    <tr>
                        <td class="font-13" style="width:56%;">RETENCION {{ number_format($pedido->porcentaje_rete_fuente) }}%</td>
                        <td class="font-13" style="width:22%;"></td>
                        <td class="font-13" style="width:22%;text-align:right;">{{ number_format($pedido->total_rete_fuente) }}</td>
                    </tr>
                @endif

                @if (count($impuestosIva) > 0)
                    @foreach ($impuestosIva as $impuestos)
                        <tr>
                            <td class="font-13" style="width:55%;">{{ $impuestos->nombre }} {{ number_format($impuestos->porcentaje) }}%</td>
                            <td class="font-13" style="width:22%;">{{ number_format($impuestos->base) }}</td>
                            <td class="font-13" style="width:22%;text-align:right;">{{ number_format($impuestos->total) }}</td>
                        </tr>
                    @endforeach
                @endif

                <tr>
                    <td class="font-13" style="width:56%;">TOTAL: </td>
                    <td class="font-13" style="width:22%;"></td>
                    <td class="font-13" style="width:22%;text-align:right;">{{ number_format($pedido->total_factura) }}</td>
                </tr>
                <tr>
                    <td class="spacer-10 padding5"></td>
                </tr>
            </tbody>
        </table>

        <table class="width-100">
            <thead class="center-item">
                <tr>
                    <th class="border-dashed font-13" style="width:100%;"> <b class="font-13">DATOS DEL CLIENTE</b></th>
                </tr>
            </thead>
        </table>

        <table class="width-100">
            <tbody>
                <tr>
                    <td class="font-13" class="spacer-10 padding5"></td>
                </tr>
                <tr>
                    <td class="font-13" style="width:100%;">{{ $cliente->nombre_completo }}</td>
                </tr>
                <tr>
                    <td class="font-13" style="width:100%;">{{ $cliente->tipo_documento->nombre }} N° {{ $cliente->numero_documento }}</td>
                </tr>
                @if ($cliente->direccion)
                    <tr>
                        <td class="font-13" style="width:100%;">{{ $cliente->direccion }}</td>
                    </tr>
                @endif
                @if ($cliente->telefono_1)
                    <tr>
                        <td class="font-13" style="width:100%;"> TELEFONO: {{ $cliente->telefono_1 }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="font-13" class="spacer-10 padding5"></td>
                </tr>
            </tbody>
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