<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Auxiliar</title>

    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", "Lato", Arial, sans-serif;
            line-height: 1.4;
            font-size: 9px; /* Reducido para mejor ajuste */
            width: 100%;
            text-transform: uppercase;
            color: #2c3e50;
            background-color: #ffffff;
        }

        .text-title {
            font-size: 15px;
            font-weight: bold;
        }

        .spacer {
            height: 15px; /* Reducido */
        }

        .valor {
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .font-size-small {
            font-size: 10px;
        }

        .font-size-medium {
            font-size: 14px;
        }

        .tabla-detalle-factura {
            font-size: 8px;
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .header-factura th {
            border: 1px solid #ddd;
            background-color: #58978423;
            padding: 6px 3px; /* Padding reducido */
            font-weight: bold;
            text-align: center;
        }

        .th-header {
            border: 1px solid #ddd;
            background-color: #58978423 !important;
            padding: 6px 3px; /* Padding reducido */
            font-weight: bold;
            text-align: center;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid !important;
            page-break-after: auto;
        }

        tbody {
            page-break-inside: auto;
        }

        td {
            page-break-inside: avoid;
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
        }

        .logo img {
            height: 70px; /* Reducido */
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
            line-height: 2.5em; /* Reducido */
        }

        .numero-consecutivo {
            color: #8d00ff;
            font-size: 2.2em; /* Reducido */
        }
        
        .generado {
            width: 40%;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 8px;
            line-height: 12px;
        }

        /* ESTILOS PARA LAS CELDAS - MANTENIENDO TUS COLORES EXACTOS */
        .bg-white { background-color: #FFFFFF; }
        .bg-alerta { background-color: #FF00004D; color: #000000; }
        .bg-nits-totales { background-color: #9BD8E9; font-weight: 600; }
        .bg-nits { background-color: #e2e2e2; font-weight: 600; }
        .bg-totales { background-color: #000000; font-weight: bold; color: #FFFFFF; }
        .bg-nivel-1 { background-color: #212329; font-weight: bold; color: #FFFFFF; }
        .bg-nivel-2 { background-color: #4D4F54; font-weight: bold; color: #FFFFFF; }
        .bg-nivel-4 { background-color: #33849E; font-weight: 600; color: #FFFFFF; }
        .bg-nivel-6 { background-color: #9BD8E9; font-weight: 600; }
        .bg-nivel-detalle { background-color: #9BD8E9; font-weight: 500; }

        /* ALINEACIONES MEJORADAS */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }

        /* ANCHOS DE COLUMNA OPTIMIZADOS */
        .col-cuenta { width: 40px; }
        .col-nombre { width: 100px; }
        .col-doc-nit { width: 70px; }
        .col-nombre-nit { width: 120px; }
        .col-ccostos { width: 50px; }
        .col-factura { width: 60px; }
        .col-saldo { width: 60px; }
        .col-debito { width: 60px; }
        .col-credito { width: 60px; }
        .col-comprobante { width: 60px; }
        .col-consecutivo { width: 30px; }
        .col-fecha { width: 60px; }
        .col-concepto { width: 170px; }

        /* MÁRGENES PARA EVITAR SUPERPOSICIÓN */
        .content-wrapper {
            margin-bottom: 60px; /* Espacio para el footer */
        }
    </style>
</head>

<body class="main">

    <div class="content-wrapper">
        <table>
            <thead>
                <tr>
                    <td class="spacer padding5"></td>
                </tr>
                <tr>
                    <td colspan="14" class="padding5">
                        <table>
                            <tr>
                                <td class="consecutivo padding5">
                                    <h4>{{ $nombre_informe }}</h4>
                                </td>
                                <td class="empresa padding5">
                                    <h1 style="font-size: 16px; margin: 0;">{{ $empresa->razon_social }}</h1>
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
                                    <img style="height:70px; width:70px;" src="https://app.portafolioerp.com/img/logo_contabilidad.png">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </thead>
        </table>

        <!-- INFORMACIÓN DE FILTROS -->
        <table style="margin: 10px 0;">
            <tr>
                <td class="padding3 text-bold font-size-small">Fecha: {{ $auxiliar->fecha_desde ?? 'No especificado' }} al {{ $auxiliar->fecha_hasta ?? 'No especificado' }}</td>
            </tr>
            @if ($auxiliar->id_nit)
                <tr>
                    <td class="padding3 text-bold font-size-small">Nit: {{ $nit->nombre_completo ?? 'No especificado' }}</td>
                </tr>
            @endif
            @if ($auxiliar->id_cuenta)
                <tr>
                    <td class="padding3 text-bold font-size-small">Cuenta: {{ $cuenta->id ?? 'No especificado' }}</td>
                </tr>
            @endif
        </table>

        <table class="tabla-detalle-factura">
            <thead>
                <tr class="header-factura">
                    @if (!$auxiliar->id_cuenta)
                        <th class="th-header col-cuenta">CUENTA</th>
                        <th class="th-header col-nombre">NOMBRE</th>
                    @endif
                    @if (!$auxiliar->id_nit)
                        <th class="th-header col-doc-nit">DOC. NIT</th>
                        <th class="th-header col-nombre-nit">NOMBRE NIT</th>
                    @endif                    
                    <th class="th-header col-fecha">FECHA</th>
                    <th class="th-header col-factura">FACTURA</th>
                    <th class="th-header col-saldo">SAL. ANTERIOR</th>
                    <th class="th-header col-saldo">DEBITO</th>
                    <th class="th-header col-saldo">CREDITO</th>
                    <th class="th-header col-saldo">SAL. FINAL</th>
                    <th class="th-header col-comprobante">COMPROB.</th>
                    <th class="th-header col-consecutivo">CONSEC</th>
                    <th class="th-header col-ccostos">C. COSTOS</th>
                    <th class="th-header col-concepto">CONCEP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auxiliares as $auxiliarD)
                    @php
                        $clase = 'bg-white';
                        
                        // Manteniendo exactamente tus condiciones originales
                        if($auxiliarD->detalle_group == 'nits') {
                            if(($auxiliarD->naturaleza_cuenta == 0 && intval($auxiliarD->saldo_final) < 0) || 
                            ($auxiliarD->naturaleza_cuenta == 1 && intval($auxiliarD->saldo_final) > 0)) {
                                $cuenta = substr($auxiliarD->cuenta, 0, 2);
                                if ($cuenta != '11') {
                                    $clase = 'bg-alerta';
                                }
                            } else {
                                $clase = 'bg-nits';
                            }
                        }
                        
                        elseif($auxiliarD->auxiliar) {
                            $clase = 'bg-white';
                        }
                        
                        elseif($auxiliarD->detalle_group == 'NITS-TOTALES') {
                            $clase = 'bg-nits-totales';
                        }
                        elseif($auxiliarD->detalle_group == 'NITS') {
                            $clase = 'bg-nits';
                        }
                        elseif($auxiliarD->cuenta == "TOTALES") {
                            $clase = 'bg-totales';
                        }
                        elseif(strlen($auxiliarD->cuenta) == 1) {
                            $clase = 'bg-nivel-1';
                        }
                        elseif(strlen($auxiliarD->cuenta) == 2) {
                            $clase = 'bg-nivel-2';
                        }
                        elseif(strlen($auxiliarD->cuenta) == 4) {
                            $clase = 'bg-nivel-4';
                        }
                        elseif(strlen($auxiliarD->cuenta) == 6) {
                            $clase = 'bg-nivel-6';
                        }
                        elseif($auxiliarD->detalle == 0 && $auxiliarD->detalle_group == 0) {
                            $clase = 'bg-white';
                        }
                        elseif($auxiliarD->detalle_group && !$auxiliarD->detalle) {
                            $clase = 'bg-nivel-detalle';
                        }
                        elseif($auxiliarD->detalle) {
                            $clase = 'bg-nivel-detalle';
                        }
                    @endphp
                    
                    <tr class="{{ $clase }}">
                        @include('pdf.informes.auxiliar.celdas', ['style' => $clase, 'auxiliar' => $auxiliarD, 'filter' => $auxiliar])
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- PIE DE PÁGINA -->
    <table class="footer">
        <tr>
            <td class="padding5">
                <table>
                    <tr>
                        <td class="empresa-footer font-size-small padding5">
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
                        <td class="empresa-footer-left font-size-small padding5">
                            ESTE INFORME FU&Eacute; GENERADO POR PORTAFOLIO ERP <br>
                            www.portafolioerp.com
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>