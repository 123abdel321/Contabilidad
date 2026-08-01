<html>

<head>
    <meta charset="UTF-8">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #24324A;
            background: #FFF;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
        }

        .page {

            padding: 15px;
        }

        .section {

            border: 1px solid #d9e1ec;
            border-radius: 6px;
        }

        .title-blue {

            background: #0d3b77;
            color: white;
            font-size: 15px;
            font-weight: bold;
            padding: 8px 12px;
        }

        .subtitle {

            color: #173c74;
            font-weight: bold;
        }

        .small {

            font-size: 9px;
        }

        .normal {

            font-size: 10px;
        }

        .big {

            font-size: 17px;
            font-weight: bold;
        }

        .border-bottom {

            border-bottom: 1px solid #e2e7ef;
        }

        .padding5 {

            padding: 5px;
        }

        .padding8 {

            padding: 8px;
        }

        .padding10 {

            padding: 10px;
        }

        .padding12 {

            padding: 12px;
        }

        .center {

            text-align: center;
        }

        .right {

            text-align: right;
        }

        .left {

            text-align: left;
        }

        .logo {

            width: 90px;
        }

        .logo img {

            width: 90px;
        }

        .header-company {

            font-size: 32px;
            color: #14396f;
            font-weight: bold;
        }

        .header-company-small {

            font-size: 14px;
            color: #6d7480;
        }

        .header-document {

            color: #14396f;
            font-size: 30px;
            font-weight: bold;
        }

        .document-number {

            background: #0d3b77;
            color: white;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
        }

        .card {

            border: 1px solid #d8e1ef;
            border-radius: 5px;
        }

        .card-title {

            background: #f8fafc;
            border-bottom: 1px solid #d8e1ef;
            padding: 10px;
            font-size: 15px;
            font-weight: bold;
            color: #14396f;
        }

        .label {

            width: 35%;
            color: #14396f;
            font-weight: bold;
        }

        .value {

            width: 65%;
        }

        .row-line td {

            padding: 8px;
            border-bottom: 1px solid #edf1f5;
        }

        .table-detail th {

            background: #0d3b77;
            color: white;
            padding: 9px;
            font-size: 10px;
            border: 1px solid #dfe5ef;
        }

        .table-detail td {

            padding: 9px;
            border: 1px solid #dfe5ef;
        }

        .total-box {

            border: 1px solid #d8e1ef;
        }

        .total-title {

            background: #0d3b77;
            color: white;
            padding: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        .total-row td {

            padding: 7px;
            border-bottom: 1px solid #edf1f5;
        }

        .total-final {

            background: #0d3b77;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        .footer-box {

            border: 1px solid #d8e1ef;
        }

        .signature {

            text-align: center;
            padding: 12px;
        }

        .signature-line {

            border-top: 1px solid #000;
            margin-top: 40px;
        }
    </style>

</head>

<body>

    <div class="page">

        <table>

            <tr>

                <td width="15%">

                    @if($empresa->logo)

                    <img src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}"
                        style="width:90px;">

                    @else

                    <img src="img/logo_contabilidad.png" style="width:90px;">

                    @endif

                </td>

                <td width="45%">

                    <div class="header-company">

                        {{ strtoupper($empresa->razon_social) }}

                    </div>

                    <div class="header-company-small">

                        Soluciones Administrativas

                    </div>

                    <br>

                    <table>

                        <tr>

                            <td width="20">

                                NIT

                            </td>

                            <td>

                                {{ $empresa->nit }}

                                @if($empresa->dv)

                                - {{ $empresa->dv }}

                                @endif

                            </td>

                        </tr>

                        <tr>

                            <td>

                                DIR

                            </td>

                            <td>

                                {{ $empresa->direccion }}

                            </td>

                        </tr>

                        <tr>

                            <td>

                                TEL

                            </td>

                            <td>

                                {{ $empresa->telefono }}

                            </td>

                        </tr>

                        <tr>

                            <td>

                                WEB

                            </td>

                            <td>

                                www.portafolioerp.com

                            </td>

                        </tr>

                        <tr>

                            <td>

                                MAIL

                            </td>

                            <td>

                                {{ $empresa->email }}

                            </td>

                        </tr>

                    </table>

                </td>

                <td width="40%" class="right">

                    <div class="header-document">

                        DOCUMENTO SOPORTE

                    </div>

                    <br>

                    <div class="document-number">

                        {{ $gasto->comprobante->prefijo ?? 'DS' }}-{{ str_pad($gasto->consecutivo,8,'0',STR_PAD_LEFT) }}

                    </div>

                    <br>

                    <table>

                        <tr>

                            <td class="right subtitle">

                                FECHA DE EXPEDICIÓN

                            </td>

                        </tr>

                        <tr>

                            <td class="right big">

                                {{ \Carbon\Carbon::parse($gasto->fecha_manual)->format('d / m / Y') }}

                            </td>

                        </tr>

                    </table>

                </td>

            </tr>

        </table>

        <br>

        <!-- ========================= -->
        <!-- PROVEEDOR + INFORMACIÓN -->
        <!-- ========================= -->

        <table width="100%">

            <tr>

                <td width="48%" style="padding-right:8px;">

                    <table class="card">

                        <tr>

                            <td class="card-title">

                                PROVEEDOR

                            </td>

                        </tr>

                        <tr>

                            <td class="padding12">

                                <table width="100%">

                                    <tr>

                                        <td colspan="2" style="font-size:18px;font-weight:bold;padding-bottom:15px;">

                                            {{ $proveedor ? strtoupper($proveedor->nombre_nit) : '' }}

                                        </td>

                                    </tr>

                                    <tr class="row-line">

                                        <td class="label">

                                            NIT

                                        </td>

                                        <td class="value">

                                            @if($proveedor)

                                            {{ $proveedor->numero_documento }}

                                            @if($empresa->dv)

                                            - {{ $proveedor->dv }}

                                            @endif

                                            @endif

                                        </td>

                                    </tr>

                                    <tr class="row-line">

                                        <td class="label">

                                            DIRECCIÓN

                                        </td>

                                        <td class="value">

                                            @if($proveedor)

                                            {{ $proveedor->direccion }}

                                            @endif

                                        </td>

                                    </tr>

                                    <tr class="row-line">

                                        <td class="label">

                                            CIUDAD

                                        </td>

                                        <td class="value">

                                            @if($proveedor)

                                            {{ $proveedor->ciudad }}

                                            @endif

                                        </td>

                                    </tr>

                                    <tr class="row-line">

                                        <td class="label">

                                            TELÉFONO

                                        </td>

                                        <td class="value">

                                            @if($proveedor)

                                            {{ $proveedor->telefono }}

                                            @endif

                                        </td>

                                    </tr>

                                    <tr>

                                        <td class="label" style="padding-top:8px;">

                                            CORREO

                                        </td>

                                        <td class="value" style="padding-top:8px;">

                                            @if($proveedor)

                                            {{ $proveedor->email }}

                                            @endif

                                        </td>

                                    </tr>

                                </table>

                            </td>

                        </tr>

                    </table>

                </td>

                <td width="52%">

                    <table class="card">

                        <tr>

                            <td class="card-title">

                                INFORMACIÓN DEL GASTO

                            </td>

                        </tr>

                        <tr>

                            <td class="padding12">

                                <table width="100%">

                                    <tr class="row-line">

                                        <td class="label">

                                            CENTRO DE COSTOS

                                        </td>

                                        <td class="value">

                                            @if($gasto->cecos)

                                            {{ $gasto->cecos->codigo }}

                                            -

                                            {{ $gasto->cecos->nombre }}

                                            @endif

                                        </td>

                                    </tr>

                                    <tr class="row-line">

                                        <td class="label">

                                            DOCUMENTO REFERENCIA

                                        </td>

                                        <td class="value">

                                            {{ $gasto->documento_referencia }}

                                        </td>

                                    </tr>

                                    <tr class="row-line">

                                        <td class="label">

                                            USUARIO

                                        </td>

                                        <td class="value">

                                            {{ $usuario }}

                                        </td>

                                    </tr>

                                    <tr class="row-line">

                                        <td class="label">

                                            MONEDA

                                        </td>

                                        <td class="value">

                                            COP - Peso Colombiano

                                        </td>

                                    </tr>

                                    <tr class="row-line">

                                        <td class="label">

                                            TIPO DE GASTO

                                        </td>

                                        <td class="value">

                                            {{ $gasto->comprobante->nombre }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <td class="label" style="padding-top:8px;">

                                            OBSERVACIÓN

                                        </td>

                                        <td class="value" style="padding-top:8px;">

                                            @if($gasto->observacion)

                                            {{ $gasto->observacion }}

                                            @endif

                                        </td>

                                    </tr>

                                </table>

                            </td>

                        </tr>

                    </table>

                </td>

            </tr>

        </table>

        <br>

        <!-- ================================================= -->
        <!-- DETALLE DE CONCEPTOS -->
        <!-- ================================================= -->

        <table class="table-detail">

            <thead>

                <tr>

                    <th width="5%">#</th>

                    <th width="35%">CONCEPTO</th>

                    <th width="8%">CANT.</th>

                    <th width="13%" class="right">SUBTOTAL</th>

                    <th width="10%" class="right">IVA</th>

                    <th width="10%" class="right">RETEFUENTE</th>

                    <th width="9%" class="right">RETEICA</th>

                    <th width="10%" class="right">TOTAL</th>

                </tr>

            </thead>

            <tbody>

                @foreach($detalles as $i => $detalle)

                <tr>

                    <td align="center">

                        {{ $i+1 }}

                    </td>

                    <td>

                        <strong>{{ $detalle->concepto->codigo }}</strong>

                        <br>

                        {{ strtoupper($detalle->concepto->nombre) }}

                    </td>

                    <td align="center">

                        1

                    </td>

                    <td class="right">

                        {{ number_format($detalle->subtotal,0,",",".") }}

                    </td>

                    <td class="right">

                        {{ number_format($detalle->iva_valor,0,",",".") }}

                    </td>

                    <td class="right">

                        {{ number_format($detalle->rete_fuente_valor,0,",",".") }}

                    </td>

                    <td class="right">

                        {{ number_format($detalle->rete_ica_valor,0,",",".") }}

                    </td>

                    <td class="right">

                        <strong>

                            {{ number_format($detalle->total,0,",",".") }}

                        </strong>

                    </td>

                </tr>

                @endforeach

                @if(count($detalles)==0)

                <tr>

                    <td colspan="8" style="padding:30px;text-align:center;">

                        NO EXISTEN CONCEPTOS

                    </td>

                </tr>

                @endif

            </tbody>

        </table>

        <br>

        <!-- ========================================== -->
        <!-- RESUMEN + PAGOS -->
        <!-- ========================================== -->

        <table>

            <tr>

                <td width="52%" style="padding-right:10px;">


                    <table class="card">

                        <tr>

                            <td class="card-title">

                                FORMAS DE PAGO

                            </td>

                        </tr>

                        <tr>

                            <td class="padding12">

                                <table width="100%">

                                    <tr style="font-weight:bold;border-bottom:1px solid #DDD;">

                                        <td>

                                            FORMA DE PAGO

                                        </td>

                                        <td align="right">

                                            VALOR

                                        </td>

                                    </tr>

                                    @foreach($pagos as $pago)

                                    <tr>

                                        <td style="padding-top:8px;padding-bottom:8px;">

                                            {{ $pago->forma_pago->nombre }}

                                        </td>

                                        <td align="right">

                                            {{ number_format($pago->valor,0,",",".") }}

                                        </td>

                                    </tr>

                                    @endforeach

                                    <tr>

                                        <td style="padding-top:10px;font-weight:bold;">

                                            TOTAL PAGADO

                                        </td>

                                        <td align="right" style="font-weight:bold;">

                                            {{ number_format($pagos->sum('valor'),0,",",".") }}

                                        </td>

                                    </tr>

                                </table>

                            </td>

                        </tr>

                    </table>

                </td>

                <td width="48%">


                    <table class="total-box">

                        <tr>

                            <td class="total-title">

                                RESUMEN FINANCIERO

                            </td>

                        </tr>

                        <tr>

                            <td>

                                <table width="100%">

                                    <tr class="total-row">

                                        <td class="padding8">

                                            SUBTOTAL

                                        </td>

                                        <td class="padding8 right">

                                            {{ number_format($detalles->sum('subtotal'),0,",",".") }}

                                        </td>

                                    </tr>

                                    <tr class="total-row">

                                        <td class="padding8">

                                            IVA

                                        </td>

                                        <td class="padding8 right">

                                            {{ number_format($detalles->sum('iva_valor'),0,",",".") }}

                                        </td>

                                    </tr>

                                    <tr class="total-row">

                                        <td class="padding8">

                                            RETEFUENTE

                                        </td>

                                        <td class="padding8 right">

                                            {{ number_format($detalles->sum('rete_fuente_valor'),0,",",".") }}

                                        </td>

                                    </tr>

                                    <tr class="total-row">

                                        <td class="padding8">

                                            RETEICA

                                        </td>

                                        <td class="padding8 right">

                                            {{ number_format($detalles->sum('rete_ica_valor'),0,",",".") }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <td colspan="2">

                                            &nbsp;

                                        </td>

                                    </tr>

                                    <tr class="total-final">

                                        <td class="padding10">

                                            TOTAL

                                        </td>

                                        <td class="padding10 right">

                                            {{ number_format($detalles->sum('total'),0,",",".") }}

                                        </td>

                                    </tr>

                                </table>

                            </td>

                        </tr>

                    </table>

                </td>

            </tr>

        </table>

        <br>

        <!-- ============================================= -->
        <!-- OBSERVACIONES -->
        <!-- ============================================= -->

        <table class="card">

            <tr>

                <td class="card-title">

                    OBSERVACIONES

                </td>

            </tr>

            <tr>

                <td class="padding12" style="height:70px;">

                    @if(!empty($gasto->observacion))

                    {{ strtoupper($gasto->observacion) }}

                    @else

                    SIN OBSERVACIONES

                    @endif

                </td>

            </tr>

        </table>

        <br>

        <!-- ============================================= -->
        <!-- QR + DATOS DEL DOCUMENTO -->
        <!-- ============================================= -->

        <table>

            <tr>

                <td width="28%" align="center">

                    @if(!empty($gasto->qr))

                    <img src="{{ $gasto->qr }}" style="width:120px;">

                    @else

                    <div style="
width:120px;
height:120px;
border:1px solid #CCC;
line-height:120px;
text-align:center;
color:#AAA;
">

                        QR

                    </div>

                    @endif

                </td>

                <td width="72%">

                    <table class="card">

                        <tr>

                            <td class="card-title">

                                INFORMACIÓN DEL DOCUMENTO

                            </td>

                        </tr>

                        <tr>

                            <td class="padding10">

                                <table>

                                    <tr>

                                        <td width="30%" class="subtitle">

                                            Documento

                                        </td>

                                        <td>

                                            {{ $gasto->comprobante->nombre }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <td class="subtitle">

                                            Consecutivo

                                        </td>

                                        <td>

                                            {{ $gasto->consecutivo }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <td class="subtitle">

                                            Fecha

                                        </td>

                                        <td>

                                            {{ \Carbon\Carbon::parse($gasto->fecha_manual)->format('d/m/Y') }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <td class="subtitle">

                                            Proveedor

                                        </td>

                                        <td>

                                            @if($proveedor)

                                            {{ $proveedor->nombre_nit }}

                                            @endif

                                        </td>

                                    </tr>

                                    <tr>

                                        <td class="subtitle">

                                            Generado por

                                        </td>

                                        <td>

                                            {{ $usuario }}

                                        </td>

                                    </tr>

                                    <tr>

                                        <td class="subtitle">

                                            Generado

                                        </td>

                                        <td>

                                            {{ $fecha_pdf }}

                                        </td>

                                    </tr>

                                </table>

                            </td>

                        </tr>

                    </table>

                </td>

            </tr>

        </table>

        <br><br><br>

        <!-- ============================================= -->
        <!-- FIRMAS -->
        <!-- ============================================= -->

        <table width="100%">

            <tr>

                <td width="33%" align="center">

                    <div style="height:45px;"></div>

                    <div style="border-top:1px solid #000;width:220px;margin:auto;"></div>

                    <br>

                    <strong>ELABORÓ</strong>

                </td>

                <td width="34%" align="center">

                    <div style="height:45px;"></div>

                    <div style="border-top:1px solid #000;width:220px;margin:auto;"></div>

                    <br>

                    <strong>REVISÓ</strong>

                </td>

                <td width="33%" align="center">

                    <div style="height:45px;"></div>

                    <div style="border-top:1px solid #000;width:220px;margin:auto;"></div>

                    <br>

                    <strong>APROBÓ</strong>

                </td>

            </tr>

        </table>

        <br><br>

        <!-- ============================================= -->
        <!-- FOOTER -->
        <!-- ============================================= -->

        <table style="border-top:2px solid #0d3b77;">

            <tr>

                <td width="50%" style="padding-top:10px;">

                    <strong>{{ strtoupper($empresa->razon_social) }}</strong>

                    <br>

                    @if($empresa->direccion)

                    {{ $empresa->direccion }}

                    <br>

                    @endif

                    @if($empresa->telefono)

                    TEL: {{ $empresa->telefono }}

                    @endif

                    @if($empresa->email)

                    <br>

                    {{ $empresa->email }}

                    @endif

                </td>

                <td width="50%" align="right" style="padding-top:10px;">

                    Documento generado por

                    <strong>PORTAFOLIO ERP</strong>

                    <br>

                    www.portafolioerp.com

                    <br>

                    {{ $fecha_pdf }}

                </td>

            </tr>

        </table>

    </div>

    <script type="text/php">

if(isset($pdf)){

    $font=$fontMetrics->get_font("Helvetica","normal");

    $pdf->page_text(

        520,

        810,

        "Página {PAGE_NUM} de {PAGE_COUNT}",

        $font,

        9

    );

}

</script>

</body>

</html>