<table class="footer" style="width: 100%;">
    <tr>
        <td class="padding5">
            <div class="box" style="width: 100%;">
                <table class="box-table" style="width: 100%;">
                    <tr>
                        <td class="aling-top padding5" style="width: 40%;">
                            <table class="box-table" style="border-right: solid 1px #d5d5d5; width: 100%;">
                                <tr class="box-title-row">
                                    <td class="icon-normal" style="padding-left: 15px; padding-right: 0px; text-align-last: center;">
                                        <img src="{!! icon('shield') !!}" width="25">
                                    </td>
                                    <td style="padding: 0px;">
                                        <table style="width: 100%;">
                                            <tr><td style="padding: 0px; font-size: 8px; text-transform: capitalize; color: #003165;">Documento generado por</td></tr>
                                            <tr><td style="padding: 0px; font-size: 14px; text-transform: uppercase; color: #003165; padding-top: 3px;">Portafolio ERP</td></tr>
                                            <tr><td style="padding: 0px; font-size: 8px; text-transform: lowercase; color: #003165;">www.portafolioerp.com</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="aling-top padding5" style="width: 40%;">
                            <table class="box-table" style="border-right: solid 1px #d5d5d5; width: 100%;">
                                <tr class="box-title-row">
                                    <td class="icon-normal" style="padding-right: 5px; text-align-last: center;">
                                        <img src="{!! icon('calendar') !!}" width="25">
                                    </td>
                                    <td style="padding: 0px; padding-right: 10px;">
                                        <table style="width: 100%;">
                                            <tr><td style="padding: 0px; font-size: 10px; text-transform: uppercase; color: #003165; padding-top: 3px;">Fecha y hora de generación</td></tr>
                                            <tr style="padding-top: 2px;"><td style="padding: 0px; font-size: 10px; text-transform: lowercase; color: #003165;">{{ $fecha_pdf }}</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="aling-top padding5" style="width:20%;">
                            <table class="box-table" style="width: 100%;">
                                <tr class="box-title-row">
                                    <td class="icon-normal" style="padding-right:5px; text-align:center;">
                                        <img src="{!! icon('calendar') !!}" width="25">
                                    </td>
                                    <td style="padding:0;">
                                        <table style="width: 100%;">
                                            <tr><td style="padding:0; font-size:10px; text-transform:uppercase; color:#003165;">PÁGINA</td></tr>
                                            <tr><td style="padding:0;font-size:10px;color:#003165;"><span class="page-number"></span></td></tr>
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