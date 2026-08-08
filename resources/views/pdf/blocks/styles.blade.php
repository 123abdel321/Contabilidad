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
            page-break-inside: auto;
        }

        thead {
            display: table-header-group;
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
        FOOTER FIJO
        ============================================================ */
        @page {
            margin: 25px 25px 100px 25px;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -70px;
            width: 100%;
            font-size: 8px;
            line-height: 13px;
        }

        .footer table {
            width: 100%;
            border-collapse: collapse;
        }

        .page-number:before {
            content: counter(page);
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

        .detalle-seccion {
            page-break-inside: auto;
        }

        .tabla-paginada {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-paginada thead {
            display: table-header-group;
        }

        .tabla-paginada tbody {
            display: table-row-group;
        }

        .tabla-paginada tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
    </style>

</head>