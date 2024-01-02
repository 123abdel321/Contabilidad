<html>

	<head>
		<meta charset="UTF-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Facturas informe Z POS</title>

        <style>
            body {
				margin: 0;
				font-family: "Lato", sans-serif;
				line-height: 16px;
				width: 100%;
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

            .font-14 {
                font-size:14;
            }

            .border-dashed {
                border-bottom: 1px solid #000;
                border-bottom-style: dashed;
            }
        </style>

    </head>

    <body onload="">
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
                    {{ $empresa->nit }} - {{ $empresa->dv }}
                </th></tr>
                <tr><th class="font-12 spacer">
                    <b >INFORME DE CAJA</b>
                </th></tr>
                <tr><th class="font-12 center-item">
                    Fecha {{ $fecha_hasta }}
                </th></tr>
                <tr><th class="font-12 center-item">
                    Bodega {{ $bodega->codigo }} {{ $bodega->nombre }}
                </th></tr>
                <tr><th class="font-12 center-item">
                    Resolución {{ $resolucion->codigo }} {{ $resolucion->nombre }}
                </th></tr>
            </thead>
            
        </table>

        <table class="width-100">
            <thead class="center-item">
                <tr>
                    <th class="border-dashed font-13 spacer" style="width:100%;"> <b class="font-13"></b></th>
                </tr>
            </thead>
        </table>

        <table class="width-100">
            <tbody>
                <tr>
                    <td class="font-13" style="width:55%;">RANGOS DE FACTURA</td>
                    <td class="font-13" style="width:45%; text-align:right;">{{ $rango_facturas }}</td>
                </tr>
                <tr>
                    <td class="font-13" style="width:55%;">FACTURAS</td>
                    <td class="font-13" style="width:45%; text-align:right;">{{ $count_facturas }}</td>
                </tr>
                <tr>
                    <td class="font-13" style="width:55%;">DEVOLUCIONES</td>
                    <td class="font-13" style="width:45%; text-align:right;">{{ $devoluciones }}</td>
                </tr>
                <tr>
                    <td class="font-13" style="width:55%;">DEVOLCUIONES EFECTIVO</td>
                    <td class="font-13" style="width:45%; text-align:right;">{{ $devoluciones_efectivo }}</td>
                </tr>
                <tr>
                    <td class="font-13" style="width:55%;">DEVOLUCIÓN TOTAL</td>
                    <td class="font-13" style="width:45%; text-align:right;">{{ $devoluciones_generales }}</td>
                </tr>
            </tbody>
        </table>

        <table class="width-100">
            <thead class="center-item">
                <tr>
                    <th class="border-dashed font-13 spacer-10" style="width:100%;"> <b class="font-13"></b></th>
                </tr>
            </thead>
        </table>

        <table class="width-100">
            <tbody>
                @foreach ($formas_pagos as $forma_pago)
                    <tr>
                        <td class="font-13" style="width:55%;">{{ $forma_pago->forma_pago->nombre }}</td>
                        <td class="font-13" style="width:45%; text-align:right;">{{ number_format($forma_pago->valor_sum) }}</td>
                    </tr>
                @endforeach
                @foreach ($formas_pagos_sin_uso as $forma_pago)
                    <tr>
                        <td class="font-13" style="width:55%;">{{ $forma_pago->nombre }}</td>
                        <td class="font-13" style="width:45%; text-align:right;"> 0,00 </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="width-100">
            <thead class="center-item">
                <tr>
                    <th class="border-dashed font-13 spacer-10" style="width:100%;"> <b class="font-13"></b></th>
                </tr>
            </thead>
        </table>

        <table class="width-100">
            <tbody>
                @foreach ($ventas_iva as $venta_iva)
                    <tr>
                        <td class="font-13" style="width:55%;">{{ $venta_iva->cuenta_iva->impuesto->nombre }} {{ $venta_iva->cuenta_iva->impuesto->porcentaje }}%</td>
                        <td class="font-13" style="width:45%; text-align:right;"> {{ number_format($venta_iva->iva_valor) }} </td>
                    </tr>
                @endforeach
                <tr>
                    <td class="font-13" style="width:55%;">TOTAL IVA</td>
                    <td class="font-13" style="width:45%; text-align:right;"> {{ number_format($ventas->sum('total_iva')) }} </td>
                </tr>
            </tbody>
        </table>

        <table class="width-100">
            <thead class="center-item">
                <tr>
                    <th class="border-dashed font-13 spacer-10" style="width:100%;"> <b class="font-13">MOVIMIENTO DE CAJA</b></th>
                </tr>
            </thead>
        </table>

        <table class="width-100">
            <thead>
                <tr>
                    <th class="font-13 spacer" style="width:55%;"><b class="font-13">DETALLE INGRESOS</b></th>
                </tr>
            </thead>
        </table>

        <table class="width-100">
            <thead>
                <tr>
                    <th class="font-13" style="width:20%;"><b class="font-13">DOCUMENTO</b></th>
                    <th class="font-13" style="width:60%;"><b class="font-13">CONCEPTO</b></th>
                    <th class="font-13" style="width:20%;"><b class="font-13">VALOR</b></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos_ingresos as $pago_ingreso)
                    <tr>
                        <td class="font-13" style="width:20%;">{{ $pago_ingreso->venta->consecutivo }}</td>
                        <td class="font-13" style="width:60%; text-align:center;">VENTA: {{ $pago_ingreso->venta->cliente->nombre_completo }} {{ $pago_ingreso->venta->cliente->numero_documento }} </td>
                        <td class="font-13" style="width:20%; text-align:right;"> {{ number_format($pago_ingreso->valor) }} </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="width-100">
            <tbody>
                <tr>
                    <td class="font-13" style="width:55%;"><b class="font-13">TOTAL INGRESOS</b></td>
                    <td class="font-13" style="width:45%; text-align:right;"><b class="font-13">{{ number_format($pagos_ingresos->sum('valor')) }}</b></td>
                </tr>
            </tbody>
        </table>

        <table class="width-100">
            <thead>
                <tr>
                    <th class="font-13 spacer" style="width:55%;"><b class="font-13">DETALLE EGRESOS</b></th>
                </tr>
            </thead>
        </table>

        <table class="width-100">
            <thead>
                <tr>
                    <th class="font-13" style="width:20%;"><b class="font-13">DOCUMENTO</b></th>
                    <th class="font-13" style="width:60%;"><b class="font-13">CONCEPTO</b></th>
                    <th class="font-13" style="width:20%;"><b class="font-13">VALOR</b></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos_egresos as $pago_egreso)
                    <tr>
                        <td class="font-13" style="width:20%;">{{ $pago_egreso->venta->consecutivo }}</td>
                        <td class="font-13" style="width:60%; text-align:center;">DEVOLUCIÓN: {{ $pago_egreso->venta->cliente->nombre_completo }} {{ $pago_egreso->venta->cliente->numero_documento }} </td>
                        <td class="font-13" style="width:20%; text-align:right;"> {{ number_format($pago_egreso->valor) }} </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="width-100">
            <tbody>
                <tr>
                    <td class="font-13" style="width:55%;"><b class="font-13">TOTAL EGRESOS</b></td>
                    <td class="font-13" style="width:45%; text-align:right;"><b class="font-13">{{ number_format($pagos_egresos->sum('valor')) }}</b></td>
                </tr>
            </tbody>
        </table>
        
        <table class="width-100">
            <thead class="center-item">
                <tr>
                    <th class="border-dashed font-13 spacer" style="width:100%;"> <b class="font-13"></b></th>
                </tr>
            </thead>
        </table>

        <table class="width-100 spacer-10">
            <tbody>
                <tr>
                    <td class="font-14" style="width:100%; text-align:center;"><b class="font-14">SALDO ANTERIOR</b></td>
                </tr>
                <tr>
                    <td class="font-14" style="width:50%; text-align:center;"><b class="font-14"> {{ number_format($saldo_anterior_total) }} </b></td>
                </tr>
            </tbody>
        </table>

        <table class="width-100">
            <tbody>
                <tr>
                    <td class="font-14" style="width:100%; text-align:center;"><b class="font-14">TOTAL SALDO</b></td>
                </tr>
                <tr>
                    <td class="font-14" style="width:50%; text-align:center;"><b class="font-14"> {{ number_format($saldo_total) }} </b></td>
                </tr>
            </tbody>
        </table>

        <table class="width-100">
            <thead class="center-item">
                <tr>
					<td class="spacer padding5"></td>
				</tr>
                <tr>
                    <td class="font-13">
                        {{ $fecha_pdf }}
                    </td>
                </tr>
                <tr>
                    <td class="font-13">
                        Esta factura fue generado por Portafolio ERP
                    </td>
                </tr>
                <tr>
                    <td class="font-13">
                        www.portafolioerp.com
                    </td>
                </tr>
            </thead>
        </table>

    </body>

</html>