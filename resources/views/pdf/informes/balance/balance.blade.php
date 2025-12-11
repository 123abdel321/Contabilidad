<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title></title>

        <style>
            body {
                margin: 0;
                font-family: Arial, "Segoe UI", "Lato", sans-serif;
                line-height: 1.4;
                font-size: 10px; /* Tamaño de fuente ligeramente menor */
                width: 100%;
                text-transform: uppercase;
                color: #2c3e50;
                background-color: #ffffff;
            }

            .text-title {
                font-size: 11px; /* Ajuste */
                font-weight: 500;
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
                height: 10px; /* Reducción de espacio */
            }

            .valor {
                text-align: right;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            .table-detail {
                font-size: 10px;
                width: 100%;
                border-collapse: collapse;
                height: 100%;
            }

            .header-factura > th {
                border: 1px solid #ddd;
                background-color: #2c3e50; /* Color oscuro profesional */
                color: #ffffff;
                font-size: 11px;
                font-weight: bold;
                padding: 5px 3px; /* Relleno compacto */
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
                padding: 2px; /* Relleno muy compacto para cabecera */
            }

            .logo {
                width: 25%;
                text-align: center;
                vertical-align: middle;
                margin: 0px auto;
            }

            .logo img {
                height: 70px; /* Reducción de altura del logo */
            }

            .empresa {
                text-align: center;
                width: 50%;
            }
            /* Estilo para comprimir el título de la empresa */
            .empresa h1 {
                font-size: 16px;
                margin: 0 0 5px 0; 
                line-height: 1.2;
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
                line-height: 1em; /* Ajuste para reducir altura */
            }

            .consecutivo h2 {
                font-size: 12px;
                margin: 0;
            }

            .numero-consecutivo {
                color: #8d00ff;
                font-size: 1.8em; /* Reducción de tamaño */
                line-height: 1.2;
            }
            
            .generado {
                width: 40%;
            }

            .footer {
                position: fixed;
                bottom: 20px; /* Subida ligera del footer */
                line-height: 12px; /* Reducción de espacio */
                font-size: 7px;
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


            .info-fecha {
                width: 100%;
                margin-top: 5px; 
                margin-bottom: 5px; /* Espacio para separar de la tabla de detalles */
                border-top: 1px solid #ddd; /* Línea sutil en la parte superior */
                border-bottom: 1px solid #ddd; /* Línea sutil en la parte inferior */
            }

            .info-fecha td {
                font-size: 11px; /* Destacar ligeramente */
                font-weight: bold; /* Hacer el texto más legible */
                padding: 5px 0 5px 0; /* Relleno superior/inferior para darle altura */
                text-align: center; /* Centrar el rango de fechas */
            }

        </style>

    </head>

    <body class="main">

        <table>
            <thead>
                <tr>
                    <td class="spacer"></td> </tr>
                <tr>
                    <td colspan="7 padding3">
                        <table>
                            <tr>
                                <td class="consecutivo padding3"> <h2> {{ $titulo }} </h2>
                                    </td>
                                <td class="empresa padding3"> <h1>{{ $empresa->razon_social }}</h1>
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
                                
                                <td class="logo padding3"> @if ($empresa->logo)
                                        <img style="height:70px;" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}">
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

        <table class="info-fecha">
            <tr>
                <td>
                    RANGO DE FECHAS: {{ $cabeza->fecha_desde ?? 'NO ESPECIFICADO' }} AL {{ $cabeza->fecha_hasta ?? 'NO ESPECIFICADO' }}
                </td>
            </tr>
        </table>

        <table class="tabla-detalle-factura">
            <thead class="">
                <tr>
                    <td class="spacer"></td>
                </tr>
                <tr class="header-factura"> <th>CUENTA</th>
                    <th>NOMBRE</th>
                    <th>SAL. ANTERIOR</th>
                    <th>DEBITO</th>
                    <th>CREDITO</th>
                    <th>SAL. FINAL</th>
                </tr>
            </thead>
            <tbody class="">
                @foreach ($balances as $balance)
                    @php
                        $style = '';
                        
                        if($balance->cuenta == 'TOTALES') {
                            $style = 'background-color: #000000; font-weight: bold; color: #FFFFFF;';
                        }
                        elseif($balance->auxiliar) {
                            $style = '';
                        }
                        elseif($balance->balance) {
                            $style = 'background-color: rgba(64, 164, 209, 0.1);';
                        }
                        elseif(strlen($balance->cuenta) == 1) {
                            $style = 'background-color: #212329; font-weight: 700; color: white;';
                        }
                        elseif(strlen($balance->cuenta) == 2) {
                            $style = 'background-color: rgba(33, 35, 41, 0.7); font-weight: 700; color: white;';
                        }
                        elseif(strlen($balance->cuenta) == 4) {
                            $style = 'background-color: #33849e; font-weight: 600; color: white;';
                        }
                        elseif(strlen($balance->cuenta) == 6) {
                            $style = 'background-color: #9BD8E9; font-weight: 600;';
                        }
                        else {
                            $style = 'background-color: rgba(33, 35, 41, 0.1); font-weight: 700;';
                        }
                    @endphp
                    
                    <tr>
                        @include('pdf.informes.balance.celdas', ['style' => $style, 'balance' => $balance])
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