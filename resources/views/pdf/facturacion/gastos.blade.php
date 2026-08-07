<html>


    @include('pdf.facturacion.component.header')

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
                                    <div class="header-gastos-titulo">{{ $titulo_captura }}</div>
                                    <div class="spacer-sm"></div>
                                    <div class="header-gastos-consecutivo">{{ $documento->consecutivo }}</div>
                                    <div class="spacer-sm"></div>
                                    <table style="border: none; width: auto; margin: 0 auto;">
                                        <tr>
                                            <td style="border:none; padding-right: 5px;">
                                                <img src="{!! icon('calendar') !!}" width="16">
                                            </td>
                                            <td style="border:none; text-align: left;">
                                                <div class="header-gastos-fecha-label">Fecha de expedición</div>
                                                <div class="header-gastos-fecha-valor">{{ $documento->fecha_manual }}</div>
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
                                    <td colspan="{{ count($tabla['columnas']) }}" class="text-align-left">
                                        {{ $tabla['titulo'] ?? 'DETALLE' }}
                                    </td>
                                </tr>
                                <tr class="box-title-row">
                                    @foreach ($tabla['columnas'] as $columna)
                                        <td style="text-align: {{ $columna['align'] ?? 'left' }}; 
                                                width: {{ $columna['width'] ?? 'auto' }}; 
                                                padding-left:10px; 
                                                padding-right:10px;">
                                            {{ $columna['label'] }}
                                        </td>
                                    @endforeach
                                </tr>
                                @foreach ($tabla['filas'] as $fila)
                                    <tr class="border-solid">
                                        @foreach ($tabla['columnas'] as $columna)
                                            @php
                                                $valor = $fila[$columna['key']] ?? '';
                                                if (isset($columna['formatter']) && $columna['formatter'] == 'number') {
                                                    $valor = number_format($valor);
                                                }
                                            @endphp
                                            <td style="text-align: {{ $columna['align'] ?? 'left' }}; 
                                                    padding-left:10px; 
                                                    padding-right:10px;">
                                                {{ $valor }}
                                            </td>
                                        @endforeach
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
                                                    <div class="son-texto"><b>SON:</b> {{ $monto_letras }}</div>
                                                @endif
                                                @if(!empty($observacion))
                                                    <div class="son-texto"><b>OBSERVACIÓN:</b> {{ $observacion }}</div>
                                                @endif
                                            </td>

                                            <td class="aling-top" style="width: 45%;">
                                                <div class="box">
                                                    <table class="box-table" style="margin-left: auto;">
                                                        <tr class="resumen-title">
                                                            <td colspan="2">{{ $resumen['titulo'] ?? 'RESUMEN' }}</td>
                                                        </tr>
                                                        @foreach ($resumen['filas'] as $filaResumen)
                                                            @php
                                                                $valorResumen = $filaResumen['value'] ?? 0;
                                                                if (isset($filaResumen['formatter']) && $filaResumen['formatter'] == 'number') {
                                                                    $valorResumen = number_format($valorResumen);
                                                                }
                                                                $claseTr = $filaResumen['class'] ?? 'box-body';
                                                            @endphp
                                                            <tr class="{{ $claseTr }}">
                                                                <td>{{ $filaResumen['label'] }}</td>
                                                                <td class="valor">{{ $valorResumen }}</td>
                                                            </tr>
                                                        @endforeach
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

                            @if ($qr_code_port)
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
                                                                                src="{{ $qr_code_port }}"
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
                            @endif
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

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

    </body>


</html>