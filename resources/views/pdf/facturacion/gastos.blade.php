<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>

    <style>
        /* ============================================================
           NOTA: dompdf NO soporta flexbox ni grid.
           Todo el layout se hace con <table>, igual que tu plantilla original.
           ============================================================ */

        body {
            margin: 0;
            font-family: "Lato", sans-serif;
            line-height: 16px;
            font-size: 10px;
            width: 100%;
            text-transform: uppercase;
            color: #1c2733;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        tr {
            page-break-inside: avoid
        }

        thead {
            display: table-header-group
        }

        .padding5 { padding: 5px; }
        .padding3 { padding: 3px; }
        .spacer { height: 12px; }
        .spacer-sm { height: 6px; }

        .valor { text-align: right; }
        .text-align-center { text-align: center; }
        .text-align-left { text-align: left; }
        .text-align-right { text-align: right; }
        .aling-top { vertical-align: top; }
        .aling-middle { vertical-align: middle; }

        /* ============================================================
           PALETA / MARCA
           ============================================================ */
        :root {
            /* dompdf ignora :root, se deja solo de referencia */
        }
        /* Navy = #003165  |  Fondo suave = #f4f6f8  |  Borde = #d8dde3 */

        /* ============================================================
           HEADER
           ============================================================ */
        .header-empresa-nombre {
            font-size: 18px;
            font-weight: bold;
            color: #003165;
            text-transform: none;
        }
        .header-empresa-datos {
            font-size: 9px;
            line-height: 1.5;
            color: #333;
            text-transform: none;
        }
        .header-gastos-titulo {
            font-size: 18px;
            font-weight: bold;
            color: #003165;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-gastos-consecutivo {
            font-size: 16px;
            font-weight: bold;
            color: #ffffff;
            background-color: #003165;
            text-transform: uppercase;
            padding: 6px 10px;
            border-radius: 6px;
        }
        .header-gastos-fecha-label {
            font-size: 9px;
            font-weight: bold;
            color: #003165;
            text-transform: uppercase;
        }
        .header-gastos-fecha-valor {
            font-size: 13px;
            font-weight: bold;
            color: #1c2733;
        }
        .header-logo-img {
            height: 60px;
            max-height: 60px;
        }
        .scan-logo-img {
            height: 30px;
        }
        .scan-text {
            align-self: center;
            font-size: 13px;
            color: #003165;
            font-weight: bold;
            align-self: center;
        }
        .header-tabla-interna {
            border: none;
            width: 100%;
        }
        .header-tabla-interna td {
            border: none;
            vertical-align: middle;
        }
        .header-logo-celda {
            width: 70px;
        }
        .border-right {
            border-right: solid 1px #d8dde3;
        }

        /* ============================================================
           CAJAS CON TITULO (PROVEEDOR / GASTO / CONCEPTOS / PAGOS ...)
           ============================================================ */
        .box {
            border: 1px solid #d8dde3;
            border-radius: 6px;
            overflow: hidden;
        }
        .box-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .box-title-header {
            background-color: #003165;
            color: #FFF;
            font-weight: bold;
            font-size: 11px;
            padding: 8px 10px;
            letter-spacing: .5px;
        }
        .box-title-row td:first-child {
            border-top-left-radius: 6px;
        }
        .box-title-row td:last-child {
            border-top-right-radius: 6px;
        }
        .box-body tr:last-child td:first-child {
            border-bottom-left-radius: 6px;
        }
        .box-body tr:last-child td:last-child {
            border-bottom-right-radius: 6px;
        }
        .box-title-row td {
            font-weight: bold;
            font-size: 11px;
            padding: 8px 10px;
            letter-spacing: .5px;
        }
        .box-title-row .icon-cell {
            width: 15px;
            background-color: #003165;
            border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
        }
        .box-body td {
            padding: 3px 10px;
            font-size: 10px;
            vertical-align: middle;
        }
        .box-body .lbl {
            color: #5a6b7b;
            font-weight: bold;
            vertical-align: middle;
        }
        .box-body .icon {
            width: 16px;
            text-align: center;
            vertical-align: middle;
            padding-right: 4px;
        }
        .box-body .icon img {
            vertical-align: middle;
            display: inline-block;
        }
        .box-body .val {
            color: #1c2733;
            vertical-align: middle;
        }
        .box-body-title {
            font-size: 12px;
            font-weight: bold;
            color: #003165;
            padding: 8px 10px 4px;
        }

        /* ============================================================
           TABLA DE CONCEPTOS
           ============================================================ */
        .table-detail {
            border: 1px solid #d8dde3;
            border-radius: 6px;
            overflow: hidden;
        }
        .header-factura th {
            background-color: #003165;
            color: #ffffff;
            font-size: 9px;
            padding: 8px 6px;
            border-right: 1px solid #ffffff33;
        }
        .detalle-factura td {
            padding: 8px 6px;
            font-size: 10px;
        }

        /* ============================================================
           RESUMEN FINANCIERO
           ============================================================ */
        .resumen-box {
            border: 1px solid #d8dde3;
            border-radius: 6px;
            width: 260px;
        }
        .resumen-title td {
            background-color: #003165;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            padding: 8px 10px;
        }
        .resumen-row td {
            padding: 7px 10px;
            font-size: 10px;
            border-bottom: 1px solid #d8dde3;
        }
        .resumen-total td {
            background-color: #12345f;
            color: #ffffff;
            font-weight: bold;
            font-size: 13px;
            padding: 10px;
        }
        .son-texto {
            font-size: 10px;
            padding: 8px 10px;
            border-top: 1px dashed #d8dde3;
        }

        /* ============================================================
           FORMA DE PAGO
           ============================================================ */
        .pago-table th {
            background-color: #f4f6f8;
            color: #003165;
            font-size: 8.5px;
            padding: 7px 8px;
            border-bottom: 2px solid #d8dde3;
            text-align: left;
        }
        .pago-table td {
            padding: 7px 8px;
            font-size: 10px;
        }
        .pago-table .total-row td {
            font-weight: bold;
            background-color: #f3f6fa;
            border-bottom: none;
            color: #12345f;
        }

        .pago-table .nombre-pago {
            border-top: solid 1px #e1e1e1;
            border-right: solid 1px #e1e1e1;
            border-bottom: solid 1px #e1e1e1;
        }

        .pago-table .valor-pago {
            border-top: solid 1px #e1e1e1;
            border-left: solid 1px #e1e1e1;
            border-bottom: solid 1px #e1e1e1;
        }

        /* ============================================================
           FIRMAS (elementos visuales estaticos; conecta variables si las tienes)
           ============================================================ */
        .firmas-table td {
            text-align: center;
            font-size: 9px;
            padding: 20px 10px 6px;
            border-right: 1px solid #d8dde3;
        }
        .firmas-table td:last-child {
            border-right: none;
        }
        .firma-titulo {
            font-size: 9px;
            font-weight: bold;
            color: #003165;
            margin-bottom: 24px;
        }
        .firma-linea {
            border-top: 1px solid #1c2733;
            margin: 0 15px 4px;
        }
        .firma-nombre {
            font-weight: bold;
            font-size: 9px;
            text-transform: none;
        }
        .firma-rol {
            font-size: 8px;
            color: #5a6b7b;
            text-transform: none;
        }

        /* ============================================================
           FOOTER
           ============================================================ */
        .footer {
            position: fixed;
            bottom: 30px;
            left: 0;
            right: 0;
            line-height: 13px;
            font-size: 8px;
            padding-top: 6px;
        }
        .legal-footer {
            font-size: 8px;
            text-align: center;
            color: #ffffff;
            background-color: #003165;
            padding: 6px;
            text-transform: none;
        }
        .border-solid td{
            border: 1px solid #d8dde3;
        }
        .qr-box {
            justify-self: center;
        }

        .qr-box img {
            width: 110px;
            height: 110px;
        }

        .qr-text {
            text-transform: capitalize;
        }

        .page-number:before {
            content: counter(page);
        }
        
    </style>

</head>

<body class="main">

    <!-- ========================================================= -->
    <!-- HEADER                                                     -->
    <!-- ========================================================= -->
    <table>
        <thead>
            <tr>
                <td>
                    <table>
                        <tr>
                            <!-- Columna izquierda: logo + nombre empresa -->
                            <td style="width: 45%;">
                                <table class="header-tabla-interna border-right">
                                    <tr>
                                        <td class="header-logo-celda">
                                            @if ($empresa->logo)
                                                <img src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}" class="header-logo-img">
                                            @else
                                                <img class="header-logo-img" src="img/logo_contabilidad.png">
                                            @endif
                                        </td>
                                        <td class="header-empresa-nombre">
                                            {{ $empresa->razon_social }}
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <!-- Columna centro: datos de contacto -->
                            <td class="header-empresa-datos" style="width: 30%; padding-left: 12px;">
                                <table class="header-tabla-interna text-align-left">
                                    <tbody>
                                        <tr>
                                            <td style="width: 16px;"><img src="{!! icon('building') !!}" width="10"></td>
                                            <td>NIT: {{ $empresa->nit }}{{ $empresa->dv ? ' - '.$empresa->dv : '' }}</td>
                                        </tr>
                                        @if($empresa->telefono)
                                            <tr>
                                                <td><img src="{!! icon('phone') !!}" width="11"></td>
                                                <td>{{ $empresa->telefono }}</td>
                                            </tr>
                                        @endif
                                        @if($empresa->email)
                                            <tr>
                                                <td><img src="{!! icon('mail') !!}" width="11"></td>
                                                <td>{{ $empresa->email }}</td>
                                            </tr>
                                        @endif
                                        @if($empresa->direccion)
                                            <tr>
                                                <td><img src="{!! icon('location') !!}" width="11"></td>
                                                <td>{{ $empresa->direccion }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </td>

                            <!-- Columna derecha: GASTOS, consecutivo, fecha -->
                            <td class="text-align-center aling-middle" style="width: 25%;">
                                <div class="header-gastos-titulo">GASTOS</div>
                                <div class="spacer-sm"></div>
                                <div class="header-gastos-consecutivo">{{ $gasto->consecutivo }}</div>
                                <div class="spacer-sm"></div>
                                <table style="border: none; width: auto; margin: 0 auto;">
                                    <tr>
                                        <td style="border:none; padding-right: 5px;">
                                            <img src="{!! icon('calendar') !!}" width="16">
                                        </td>
                                        <td style="border:none; text-align: left;">
                                            <div class="header-gastos-fecha-label">Fecha de expedición</div>
                                            <div class="header-gastos-fecha-valor">{{ $gasto->fecha_manual }}</div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </thead>
    </table>

    <!-- ========================================================= -->
    <!-- CLIENTE / INFORMACION DEL GASTO                           -->
    <!-- ========================================================= -->
    <table>
        <tr><td class="spacer"></td></tr>
        <tr>
            <td>
                <table>
                    <tr>
                        <!-- INFORMACION DEL CLIENTE -->
                        @if($cliente)
                        <td class="aling-top padding5" style="width: 50%;">
                            <div class="box">
                                <table class="box-table">
                                    <tr class="box-title-row">
                                        <td class="icon-cell"><img src="{!! icon('user', '#FFFFFF') !!}" width="12"></td>
                                        <td colspan="2" class="text-align-left">{{ $cliente->titulo }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="box-body-title">{{ $cliente->nombre_cliente }}</td>
                                    </tr>

                                    @foreach ( $cliente->datos_adicionales AS $dato_adicional )
                                        @if ($dato_adicional->valor)
                                            <tr class="box-body">
                                                <td class="icon"><img src="{!! icon($dato_adicional->icono, '#003165') !!}" width="10"></td>
                                                <td class="lbl">{{ $dato_adicional->titulo }}</td>
                                                <td class="val">{{ $dato_adicional->valor }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    <tr><td colspan="2" class="spacer-sm"></td></tr>
                                </table>
                            </div>
                        </td>
                        @endif
                        @if($informacion_pdf)
                        <!-- INFORMACION DEL GASTO -->
                        <td class="aling-top padding5" style="width: 50%;">
                            <div class="box">
                                <table class="box-table">
                                    <tr class="box-title-row" style="padding-bottom: 5px;">
                                        <td class="icon-cell"><img src="{!! icon('file', '#FFFFFF') !!}" width="12"></td>
                                        <td colspan="2" class="text-align-left">{{ $informacion_pdf->titulo }}</td>
                                    </tr>
                                    <tr><td colspan="2" class="spacer-sm"></td></tr>
                                    @foreach ( $informacion_pdf->datos_adicionales AS $dato_adicional )
                                        @if ($dato_adicional->valor)
                                            <tr class="box-body">
                                                <td class="icon"><img src="{!! icon($dato_adicional->icono, '#003165') !!}" width="10"></td>
                                                <td class="lbl">{{ $dato_adicional->titulo }}</td>
                                                <td class="val">{{ $dato_adicional->valor }}</td>
                                            </tr>
                                        @endif
                                    @endforeach

                                    <tr><td colspan="2" class="spacer-sm"></td></tr>
                                </table>
                            </div>
                        </td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ========================================================= -->
    <!-- DETALLE DE CONCEPTOS                                      -->
    <!-- ========================================================= -->
    <table>
        <tr><td class="spacer"></td></tr>
        <tr>
            <td class="padding5">
                <div class="box">
                    <table class="box-table">
                        <tbody class="detalle-factura">
                            <tr class="box-title-header" style="background-color: #003165;">
                                <td colspan="7" class="text-align-left">DETALLE DE CONCEPTOS</td>
                            </tr>
                            <tr class="box-title-row">
                                <td style="widtd: 30%; text-align:left; padding-left:10px;">CONCEPTO</td>
                                <td style="widtd: 30%; text-align:left; padding-left:10px;">CUENTA</td>
                                <td class="valor">BASE GRAVABLE</td>
                                <td class="valor">IVA</td>
                                <td class="valor">RETENCIÓN</td>
                                <td class="valor">RETEICA</td>
                                <td class="valor" style="padding-right:10px;">TOTAL</td>
                            </tr>
                            @foreach ($detalles as $detalle)
                                <tr class="border-solid">
                                    <td style="padding-left:10px;">{{ $detalle->concepto?->codigo }} - {{ $detalle->concepto?->nombre }}</td>
                                    <td style="padding-left:10px;">{{ $detalle->concepto?->cuenta_gasto?->cuenta }} - {{ $detalle->concepto?->cuenta_gasto?->nombre }}</td>
                                    <td class="valor">{{ number_format($detalle->subtotal_neto) }}</td>
                                    <td class="valor">{{ number_format($detalle->iva_valor) }}</td>
                                    <td class="valor">{{ number_format($detalle->rete_fuente_valor) }}</td>
                                    <td class="valor">{{ number_format($detalle->rete_ica_valor) }}</td>
                                    <td class="valor" style="padding-right:10px;">{{ number_format($detalle->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- ========================================================= -->
                    <!-- SON (opcional, si tienes helper de numero a letras) / RESUMEN FINANCIERO -->
                    <!-- ========================================================= -->
                    <table>
                        <tr><td class="spacer"></td></tr>
                        <tr>
                            <td class="padding5">
                                <table>
                                    <tr>
                                        <td class="aling-top" style="width: 55%;">
                                            @if(isset($monto_letras))
                                                <div class="son-texto"><b>SON:</b> {{$monto_letras}}</div>
                                            @endif
                                                <div class="son-texto"><b>OBSERVACIÓN:</b> {{$detalles[0]->observacion}}</div>

                                        </td>
                                        <td class="aling-top" style="width: 50%;">
                                            <div class="box">
                                                <table class="box-table" style="margin-left: auto;">
                                                    <tr class="resumen-title">
                                                        <td colspan="2">RESUMEN FINANCIERO</td>
                                                    </tr>
                                                    <tr class="box-body">
                                                        <td>SUBTOTAL</td>
                                                        <td class="valor">{{ number_format($detalles->sum('subtotal')) }}</td>
                                                    </tr>
                                                    <tr class="box-body">
                                                        <td>IVA</td>
                                                        <td class="valor">{{ number_format($detalles->sum('iva_valor')) }}</td>
                                                    </tr>
                                                    <tr class="box-body">
                                                        <td>RETENCIÓN EN LA FUENTE</td>
                                                        <td class="valor">{{ number_format($detalles->sum('rete_fuente_valor')) }}</td>
                                                    </tr>
                                                    <tr class="box-body">
                                                        <td>RETEICA</td>
                                                        <td class="valor">{{ number_format($detalles->sum('rete_ica_valor')) }}</td>
                                                    </tr>
                                                    <tr class="resumen-total">
                                                        <td>TOTAL A PAGAR</td>
                                                        <td class="valor">{{ number_format($detalles->sum('total')) }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                </div>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="padding5">
                <table>
                    <tr>
                        <td class="aling-top" style="width: 60%;">
                            <!-- ========================================================= -->
                            <!-- FORMA DE PAGO                                              -->
                            <!-- ========================================================= -->
                            <table>
                                <tr>
                                    <td class="">
                                        <div class="box">
                                            <table class="box-table">
                                                <tr class="box-title-row">
                                                    <td class="icon-cell"><img src="{!! icon('money', '#FFFFFF') !!}" width="12"></td>
                                                    <td class="text-align-left">FORMA DE PAGO</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <table class="pago-table">
                                                            <tbody>
                                                                @if (count($pagos) > 0)
                                                                    @foreach ($pagos as $pago)
                                                                        <tr>
                                                                            <td class="nombre-pago">{{ $pago->forma_pago->nombre }}</td>
                                                                            <td class="valor-pago valor">{{ number_format($pago->valor) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                @endif
                                                                <tr class="total-row">
                                                                    <td>TOTAL PAGOS</td>
                                                                    <td class="valor">{{ number_format($pagos->sum('valor')) }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                            
                        <td class="aling-top" style="width: 40%;">

                            <table>
                                <tr>
                                    <td class="">
                                        <div class="box">
                                            <table class="box-table">
                                                <tr class="box-title-row">
                                                    <td class="icon-cell"><img src="{!! icon('desktop', '#FFFFFF') !!}" width="12"></td>
                                                    <td class="text-align-left">VER DOCUMENTO EN EL ERP</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <table class="pago-table">
                                                            <tr>
                                                                <td class="aling-top" style="width: 40%;">
                                                                    <div class="qr-box">

                                                                        <img
                                                                            src="{{ $qrCode }}"
                                                                            alt="QR"
                                                                        >

                                                                    </div>
                                                                </td>
                                                                <td class="aling-top" style="width: 60%;">
                                                                    <div class="qr-text">
                                                                        Escanea este código<br>
                                                                        para ver el documento<br>
                                                                        original en Portafolio ERP
                                                                    </div>
                                                                    <div style="display: flex; padding-top: 15px;">
                                                                        <img class="scan-logo-img" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/iconos_sistema/logo_contabilidad_nombre.png">
                                                                        
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- ========================================================= -->
    <!-- FIRMAS (visual - conecta tus variables de elaboró/revisó/aprobó si las tienes) -->
    <!-- ========================================================= -->
    <!-- <table>
        <tr><td class="spacer"></td></tr>
        <tr>
            <td class="padding5">
                <table class="firmas-table">
                    <tr>
                        <td style="width: 33.33%;">
                            <div class="firma-titulo">ELABORÓ</div>
                            <div class="firma-linea"></div>
                            <div class="firma-nombre">{{ $gasto->elaborado_por ?? '' }}</div>
                            <div class="firma-rol">{{ $gasto->elaborado_cargo ?? '' }}</div>
                        </td>
                        <td style="width: 33.33%;">
                            <div class="firma-titulo">REVISÓ</div>
                            <div class="firma-linea"></div>
                            <div class="firma-nombre">{{ $gasto->reviso_por ?? '' }}</div>
                            <div class="firma-rol">{{ $gasto->reviso_cargo ?? '' }}</div>
                        </td>
                        <td style="width: 33.33%;">
                            <div class="firma-titulo">APROBÓ</div>
                            <div class="firma-linea"></div>
                            <div class="firma-nombre">{{ $gasto->aprobo_por ?? '' }}</div>
                            <div class="firma-rol">{{ $gasto->aprobo_cargo ?? '' }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table> -->

    <!-- <table>
        <tr><td class="spacer"></td></tr>
        <tr>
            <td>
                <div class="legal-footer">
                    Este documento no requiere firma autógrafa según Decreto 2242 de 2015 y Resolución DIAN 000042 de 2020.
                </div>
            </td>
        </tr>
    </table> -->

    <table class="footer">
        <tr>
            <td class="padding5 ">
                <div class="box">
                    <table class="box-table">
                        <tr>
                            <td class="aling-top padding5" style="width: 40%;">
                                <table class="box-table" style="border-right: solid 1px #d5d5d5;">
                                    <tr class="box-title-row">
                                        <td class="icon-normal" style="padding-left: 15px; padding-rigth: 0px; text-align-last: center;">
                                            <img src="{!! icon('shield') !!}" width="25">
                                        </td>
                                        <td class="" style="padding: 0px;">
                                            <table class="">
                                                <tr>
                                                    <td style="padding: 0px; font-size: 8px; text-transform: capitalize; color: #003165;">
                                                        Documento generado por
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0px; font-size: 14px; text-transform: uppercase; color: #003165; padding-top: 3px;">
                                                        Portafolio ERP
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0px; font-size: 8px; text-transform: lowercase; color: #003165;">
                                                        www.portafolioerp.com
                                                    </td>
                                                </tr>
                                                
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                
                            </td>
                            <td class="aling-top padding5" style="width: 40%;">
                                <table class="box-table" style="border-right: solid 1px #d5d5d5;">
                                    <tr class="box-title-row">
                                        <td class="icon-normal" style="padding-rigth: 5px; text-align-last: center;">
                                            <img src="{!! icon('calendar') !!}" width="25">
                                        </td>
                                        <td class="" style="padding: 0px; padding-rigth: 10px;">
                                            <table class="">
                                                <tr>
                                                    <td style="padding: 0px; font-size: 10px; text-transform: uppercase; color: #003165; padding-top: 3px;">
                                                        Fecha y hora de generación
                                                    </td>
                                                </tr>
                                                <tr style="padding-top: 2px;">
                                                    <td style="padding: 0px; font-size: 10px; text-transform: lowercase; color: #003165;">
                                                        {{ $fecha_pdf }}
                                                    </td>
                                                </tr>
                                                
                                            </table>
                                        </td>
                                    </tr>
                                </table>                                
                            </td>
                            
                            <td class="aling-top padding5" style="width:20%;">
                                <table class="box-table">
                                    <tr class="box-title-row">
                                        <td class="icon-normal" style="padding-right:5px; text-align:center;">
                                            <img src="{!! icon('calendar') !!}" width="25">
                                        </td>

                                        <td style="padding:0;">
                                            <table>
                                                <tr>
                                                    <td style="padding:0; font-size:10px; text-transform:uppercase; color:#003165;">
                                                        PÁGINA
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td style="padding:0;font-size:10px;color:#003165;">
                                                        <span class="page-number"></span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- ========================================================= -->
    <!-- NUMERACION DE PAGINAS (dompdf)                             -->
    <!-- ========================================================= -->
    <!-- <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                $pdf->text(300, 800, "$PAGE_NUM / $PAGE_COUNT", $font, 8);
            ');
        }
    </script> -->

</body>

</html>