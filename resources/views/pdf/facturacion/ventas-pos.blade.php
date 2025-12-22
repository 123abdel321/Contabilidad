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

            .break-word {
				word-break: break-all;
			}

            .no-transform {
				text-transform: none;
			}
        </style>

    </head>

    <body onload="window.print()">
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

                @if ($mensaje_regimen)
                    <tr><th class="font-12">
                        {{ $mensaje_regimen }}
                    </th></tr>
                @else
                    <tr><th class="font-12">
                        FACTURA VENTA P.O.S: {{ $factura->documento_referencia }}
                    </th></tr>
                    
                    @if ($factura->resolucion)
                        <tr><th class="font-12">
                            RESOLUCION DIAN {{ $factura->resolucion->numero_resolucion }}
                        </th></tr>
                        <tr><th class="font-12">
                            DEL {{ $factura->resolucion->fecha }} N. {{ $factura->consecutivo }} DE LA #{{ $factura->resolucion->consecutivo_desde }} HASTA LA #{{ $factura->resolucion->consecutivo_hasta }}
                        </th></tr>
                        <tr><th class="font-12">
                            VIGANCIA: {{ $factura->resolucion->vigencia }} MESES
                        </th></tr>
                        <tr><th class="font-12">
                            FECHA: {{ $factura->fecha_manual }}
                        </th></tr>
                    @endif
                @endif

                <tr>
					<th class="spacer padding5"></th>
				</tr>
            </thead>
        </table>

        <table class="width-100">

            <thead class="center-item">
                <tr>
                    <th class="border-dashed" style="width:55%;"> <b class="font-13">PRODUCTO </b></th>
                    <th class="border-dashed" style="width:5%;text-align:center;"> <b class="font-13">CANT. </b></th>
                    <th class="border-dashed" style="width:20%;text-align:center;"> <b class="font-13">V. UNI. </b></th>
                    @if ($factura->total_descuento)
                        <th class="border-dashed" style="width:20%;text-align:center;"> <b class="font-13">DESC. </b></th>
                    @endif
                    <th class="border-dashed" style="width:20%;text-align:right;"> <b class="font-13">TOTAL </b></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($productos as $producto)
                    <tr>
                        <td class="font-13" style="width:55%;">{{ $producto->descripcion }}</td>
                        <td class="font-13" style="width:5%;text-align:center;">
                            @php
                                // Formatear a máximo 5 decimales y eliminar ceros
                                $formatted = number_format($producto->cantidad, 5, '.', '');
                                $formatted = rtrim($formatted, '0');
                                $formatted = rtrim($formatted, '.');
                                echo $formatted ?: '0';
                            @endphp
                        </td>
                        <td class="font-13" style="width:20%;text-align:center;">{{ number_format($producto->costo, 2) }}</td>
                        @if ($factura->total_descuento)
                            <td class="font-13" style="width:auto;text-align:center;">{{ number_format($producto->descuento_valor, 2) }}</td>
                        @endif
                        <td class="font-13" style="width:20%;text-align:right;">{{ number_format($producto->total, 2) }}</td>
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
                    <td class="font-13" style="width:22%;text-align:right;">{{ number_format($factura->subtotal, 2) }}</td>
                </tr>
                @if ($factura->total_descuento > 0)
                    <tr>
                        <td class="font-13" style="width:56%;">DESCUENTO</td>
                        <td class="font-13" style="width:22%;"></td>
                        <td class="font-13" style="width:22%;text-align:right;">{{ number_format($factura->total_descuento, 2) }}</td>
                    </tr>
                @endif

                @if ($factura->total_rete_fuente > 0)
                    <tr>
                        <td class="font-13" style="width:56%;">RETENCION {{ number_format($factura->porcentaje_rete_fuente, 2) }}%</td>
                        <td class="font-13" style="width:22%;"></td>
                        <td class="font-13" style="width:22%;text-align:right;">{{ number_format($factura->total_rete_fuente, 2) }}</td>
                    </tr>
                @endif

                @if (count($impuestosIva) > 0)
                    @foreach ($impuestosIva as $impuestos)
                        <tr>
                            <td class="font-13" style="width:55%;">{{ $impuestos->nombre }} {{ number_format($impuestos->porcentaje, 2) }}%</td>
                            <td class="font-13" style="width:22%;">{{ number_format($impuestos->base, 2) }}</td>
                            <td class="font-13" style="width:22%;text-align:right;">{{ number_format($impuestos->total, 2) }}</td>
                        </tr>
                    @endforeach
                @endif

                <tr>
                    <td class="font-13" style="width:56%;">TOTAL: </td>
                    <td class="font-13" style="width:22%;"></td>
                    <td class="font-13" style="width:22%;text-align:right;">{{ number_format($factura->total_factura) }}</td>
                </tr>
                <tr>
                    <td class="spacer-10 padding5"></td>
                </tr>
            </tbody>
        </table>

        <table class="width-100">
            <thead class="center-item">
                <tr>
                    <th class="border-dashed font-13" style="width:100%;"> <b class="font-13">FORMAS DE PAGO</b></th>
                </tr>
            </thead>
        </table>
        <table class="width-100">
            <tbody>
                @if (count($pagos) > 0)
                    @foreach ($pagos as $pago)
                        @if ($pago->forma_pago->id == 1)
                            <tr>
                                <td class="font-13" style="width:55%;">{{ $pago->forma_pago->nombre }}</td>
                                <td class="font-13" style="width:45%;text-align:right;">{{ number_format($pago->valor + $factura->total_cambio, 2) }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="font-13" style="width:55%;">{{ $pago->forma_pago->nombre }}</td>
                                <td class="font-13" style="width:45%;text-align:right;">{{ number_format($pago->valor, 2) }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif
                <tr>
                    <td class="font-13" style="width:55%;">CAMBIO:</td>
                    <td class="font-13" style="width:45%;text-align:right;">{{ number_format($factura->total_cambio, 2) }}</td>
                </tr>
                
                <tr>
                    <td class="font-13" style="width:55%;">TOTAL: </td>
                    <td class="font-13" style="width:45%;text-align:right;">{{ number_format($pagos->sum('valor'), 2) }}</td>
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

        @if ($factura->observacion)   
            <table class="width-100">
                <thead class="center-item">
                    <tr>
                        <th class="border-dashed font-13" style="width:100%;"> <b class="font-13">OBSERVACIÓN GENERAL</b></th>
                    </tr>
                </thead>
            </table>

            <table class="width-100">
                <tbody>
                    <tr>
                        <td class="font-13" style="width:100%;">{{ $factura->observacion }}</td>
                    </tr>
                </tbody>
            </table>
        @endif


        @if ($qrCode)
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
                                        <img src="{{ $qrCode }}" alt="QR Code" style="width: 150px; height: 150px;">
                                    </td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td>
                                        <p>
                                            <b>Resolución: </b> <br>
                                            AUTORIZACION {{ $factura->resolucion->numero_resolucion }} DE {{ $factura->resolucion->fecha }} DE
                                            {{ $factura->resolucion->prefijo }}{{ $factura->resolucion->consecutivo_desde }} HASTA {{ $factura->resolucion->prefijo }}{{ $factura->resolucion->consecutivo_hasta }} VIGENCIA
                                            {{ $factura->resolucion->vigencia }} MESES
                                        </p>
                                        @if ($factura->fe_codigo_identificador)
                                        <p>
                                            <b>Cufe: </b> <br>
                                            <span class="no-transform break-word">
                                                {{ $factura->fe_codigo_identificador }}
                                            </span>
                                        </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </thead>
            </table>
        @endif

        <table class="width-100">
            <thead class="center-item">
                <tr>
					<td class="spacer padding5"></td>
				</tr>
                <tr>
                    <td class="font-12">
                        {!! $observacion_general !!}
                    </td>
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