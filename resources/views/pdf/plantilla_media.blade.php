<!DOCTYPE html>
<html>
    @include('pdf.blocks.styles')

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
                                                @php $empresa = $document->getConfig()['empresa'] ?? null; @endphp
                                                @if ($empresa && $empresa->logo)
                                                    <img src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}" class="header-logo-img">
                                                @else
                                                    <img class="header-logo-img" src="img/logo_contabilidad.png">
                                                @endif
                                            </td>
                                            <td class="header-empresa-nombre">
                                                {{ $empresa->razon_social ?? '' }}
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
                                                <td>NIT: {{ $empresa->nit ?? '' }}{{ isset($empresa->dv) && $empresa->dv ? ' - '.$empresa->dv : '' }}</td>
                                            </tr>
                                            @if($empresa && $empresa->telefono)
                                                <tr>
                                                    <td><img src="{!! icon('phone') !!}" width="11"></td>
                                                    <td>{{ $empresa->telefono }}</td>
                                                </tr>
                                            @endif
                                            @if($empresa && $empresa->email)
                                                <tr>
                                                    <td><img src="{!! icon('mail') !!}" width="11"></td>
                                                    <td>{{ $empresa->email }}</td>
                                                </tr>
                                            @endif
                                            @if($empresa && $empresa->direccion)
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
                                    @foreach($document->getBlocks() as $block)
                                        @if(get_class($block) === 'App\Pdf\Blocks\HeaderBlock')
                                            {!! $block->render() !!}
                                        @endif
                                    @endforeach
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
                            @php
                                $clientBlock = null;
                                $infoBlock = null;
                                foreach($document->getBlocks() as $block) {
                                    if(get_class($block) === 'App\Pdf\Blocks\ClientBlock') $clientBlock = $block;
                                    if(get_class($block) === 'App\Pdf\Blocks\InfoBlock') $infoBlock = $block;
                                }
                            @endphp
                            @if($clientBlock)
                            <td class="aling-top padding5" style="width: 50%;">
                                {!! $clientBlock->render() !!}
                            </td>
                            @endif
                            @if($infoBlock)
                            <td class="aling-top padding5" style="width: 50%;">
                                {!! $infoBlock->render() !!}
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
                        @foreach($document->getBlocks() as $block)
                            @if(get_class($block) === 'App\Pdf\Blocks\TableBlock')
                                {!! $block->render() !!}
                            @endif
                        @endforeach

                        <!-- ========================================================= -->
                        <!-- SON / RESUMEN FINANCIERO -->
                        <!-- ========================================================= -->
                        <table>
                            <tr><td class="spacer"></td></tr>
                            <tr>
                                <td class="padding5">
                                    <table>
                                        <tr>
                                            @php
                                                $qrBlock = null;
                                                $notesBlock = null;
                                                $summaryBlock = null;
                                                $paymentsBlock = null;
                                                foreach($document->getBlocks() as $block) {
                                                    if(get_class($block) === 'App\Pdf\Blocks\QrBlock') $qrBlock = $block;
                                                    if(get_class($block) === 'App\Pdf\Blocks\NotesBlock') $notesBlock = $block;
                                                    if(get_class($block) === 'App\Pdf\Blocks\SummaryBlock') $summaryBlock = $block;
                                                    if(get_class($block) === 'App\Pdf\Blocks\PaymentsBlock') $paymentsBlock = $block;
                                                }
                                            @endphp
                                            <td class="aling-top" style="width: 40%;">
                                                @if($paymentsBlock)
                                                    {!! $paymentsBlock->render() !!}
                                                @endif
                                            </td>
                                            
                                            <td class="aling-top" style="width: 40%;">
                                                @if($summaryBlock)
                                                    {!! $summaryBlock->render() !!}
                                                @endif
                                            </td>

                                            <td class="aling-top" style="width: 20%;">
                                                @if($qrBlock)
                                                    {!! $qrBlock->render() !!}
                                                @endif
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
        <!-- FOOTER                                                    -->
        <!-- ========================================================= -->
        @foreach($document->getBlocks() as $block)
            @if(get_class($block) === 'App\Pdf\Blocks\FooterBlock')
                {!! $block->render() !!}
            @endif
        @endforeach

    </body>
</html>