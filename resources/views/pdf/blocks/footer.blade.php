<div class="footer">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>

            <td class="aling-top padding5" style="width: 40%;">
                <table class="box-table" style="border-right: solid 1px #d5d5d5; width: 100%;">
                    <tr class="box-title-row">
                        <td class="icon-normal" style="padding-right: 5px; text-align: center;">
                            <img src="{!! icon('calendar') !!}" width="25">
                        </td>

                        <td style="padding: 0px; padding-right: 10px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 0px; font-size: 10px; text-transform: uppercase; color: #003165; padding-top: 3px;">
                                        Fecha y hora de generación
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 0px; font-size: 10px; text-transform: lowercase; color: #003165;">
                                        {{ $fecha_pdf }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>

            <td class="aling-top padding5" style="width: 20%;">
                <table class="box-table" style="width: 100%;">
                    <tr class="box-title-row">
                        <td class="icon-normal" style="padding-right: 5px; text-align: center;">
                            <img src="{!! icon('calendar') !!}" width="25">
                        </td>

                        <td style="padding: 0px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 0px; font-size: 10px; text-transform: uppercase; color: #003165;">
                                        PÁGINA
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 0px; font-size: 10px; color: #003165;">
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