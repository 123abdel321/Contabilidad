@if ($qr_code)
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
                                            <img src="{{ $qr_code }}" alt="QR">
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
@endif