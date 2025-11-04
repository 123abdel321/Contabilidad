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

        .tabla-detalle-factura {
            font-size: 8px; /* Reducido para mejor ajuste */
            width: 100%;
            border-collapse: collapse;
        }

        .header-factura th {
            border: 1px solid #ddd;
            background-color: #58978423;
            padding: 6px 3px; /* Padding reducido */
            font-weight: bold;
            text-align: center;
        }

        thead {
            display: table-header-group;
        }

        tr {
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
            bottom: 20px; /* Ajustado */
            line-height: 12px; /* Reducido */
            font-size: 8px;
            width: 100%;
        }

        /* MEJORAS PARA LA TABLA PRINCIPAL */
        .tabla-detalle-factura td {
            padding: 4px 2px; /* Padding reducido y consistente */
            border: 1px solid #ddd;
            vertical-align: middle;
            line-height: 1.2;
        }

        /* ESTILOS PARA LAS CELDAS - MANTENIENDO TUS COLORES EXACTOS */
        .bg-white { background-color: #FFFFFF; }
        .bg-alerta { background-color: #FF00004D; color: #000000; }
        .bg-nits-totales { background-color: #9BD8E9; font-weight: 600; }
        .bg-nits { background-color: #9BD8E9; font-weight: 600; }
        .bg-totales { background-color: #000000; font-weight: bold; color: #FFFFFF; }
        .bg-nivel-1 { background-color: #212329; font-weight: bold; color: #FFFFFF; }
        .bg-nivel-2 { background-color: #4D4F54; font-weight: bold; color: #FFFFFF; }
        .bg-nivel-4 { background-color: #33849E; font-weight: 600; color: #FFFFFF; }
        .bg-nivel-6 { background-color: #9BD8E9; font-weight: 600; }
        .bg-nivel-detalle { background-color: #9BD8E9; font-weight: 600; }

        /* ALINEACIONES MEJORADAS */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }

        /* ANCHOS DE COLUMNA OPTIMIZADOS */
        .col-cuenta { width: 8%; }
        .col-nombre { width: 10%; }
        .col-doc-nit { width: 7%; }
        .col-nombre-nit { width: 12%; }
        .col-ccostos { width: 5%; }
        .col-factura { width: 6%; }
        .col-saldo { width: 6%; }
        .col-debito { width: 6%; }
        .col-credito { width: 6%; }
        .col-comprobante { width: 6%; }
        .col-consecutivo { width: 5%; }
        .col-fecha { width: 6%; }
        .col-concepto { width: 11%; }

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
                                    <h2>INFORME AUXILIAR</h2>
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
                                    @if ($empresa->logo)
                                        <img style="height:70px;" src="{{ $empresa->logo }}">
                                    @else
                                        <img style="height:70px;" src="https://app.portafolioerp.com/img/logo_contabilidad.png">
                                    @endif
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
                <td class="padding3 text-bold">Fecha: {{ $auxiliar->fecha_desde ?? 'No especificado' }} al {{ $auxiliar->fecha_hasta ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <td class="padding3 text-bold">Generado: {{ $fecha_pdf }} | Usuario: {{ $usuario ?? 'Sistema' }}</td>
            </tr>
        </table>

        <table class="tabla-detalle-factura">
            <thead>
                <tr class="header-factura">
                    <th class="col-cuenta">CUENTA</th>
                    <th class="col-nombre">NOMBRE</th>
                    <th class="col-doc-nit">DOC. NIT</th>
                    <th class="col-nombre-nit">NOMBRE NIT</th>
                    <th class="col-ccostos">C. COSTOS</th>
                    <th class="col-factura">FACTURA</th>
                    <th class="col-saldo">SAL. ANTERIOR</th>
                    <th class="col-saldo">DEBITO</th>
                    <th class="col-saldo">CREDITO</th>
                    <th class="col-saldo">SAL. FINAL</th>
                    <th class="col-comprobante">COMPROB.</th>
                    <th class="col-consecutivo">CONSEC</th>
                    <th class="col-fecha">FECHA</th>
                    <th class="col-concepto">CONCEP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auxiliares as $auxiliar)
                    @php
                        $clase = 'bg-white';
                        
                        // Manteniendo exactamente tus condiciones originales
                        if($auxiliar->detalle_group == 'nits') {
                            if(($auxiliar->naturaleza_cuenta == 0 && intval($auxiliar->saldo_final) < 0) || 
                            ($auxiliar->naturaleza_cuenta == 1 && intval($auxiliar->saldo_final) > 0)) {
                                $cuenta = substr($auxiliar->cuenta, 0, 2);
                                if ($cuenta != '11') {
                                    $clase = 'bg-alerta';
                                }
                            }
                        }
                        
                        elseif($auxiliar->auxiliar) {
                            $clase = 'bg-white';
                        }
                        
                        elseif($auxiliar->detalle_group == 'nits-totales') {
                            $clase = 'bg-nits-totales';
                        }
                        elseif($auxiliar->detalle_group == 'nits') {
                            $clase = 'bg-nits';
                        }
                        elseif($auxiliar->cuenta == "TOTALES") {
                            $clase = 'bg-totales';
                        }
                        elseif(strlen($auxiliar->cuenta) == 1) {
                            $clase = 'bg-nivel-1';
                        }
                        elseif(strlen($auxiliar->cuenta) == 2) {
                            $clase = 'bg-nivel-2';
                        }
                        elseif(strlen($auxiliar->cuenta) == 4) {
                            $clase = 'bg-nivel-4';
                        }
                        elseif(strlen($auxiliar->cuenta) == 6) {
                            $clase = 'bg-nivel-6';
                        }
                        elseif($auxiliar->detalle == 0 && $auxiliar->detalle_group == 0) {
                            $clase = 'bg-white';
                        }
                        elseif($auxiliar->detalle_group && !$auxiliar->detalle) {
                            $clase = 'bg-nivel-detalle';
                        }
                        elseif($auxiliar->detalle) {
                            $clase = 'bg-nivel-detalle';
                        }
                    @endphp
                    
                    <tr class="{{ $clase }}">
                        @include('pdf.informes.auxiliar.celdas', ['style' => $clase, 'auxiliar' => $auxiliar])
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