@if($qr_erp)
<div class="box" style="width: 100%;">
    <table class="box-table" style="width: 100%;">
        <tr class="box-title-row">
            <td class="icon-cell"><img src="{!! icon('desktop', '#FFFFFF') !!}" width="12"></td>
            <td class="text-align-left">VER DOCUMENTO EN EL ERP</td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="pago-table" style="width: 100%;">
                    <tr>
                        <td class="aling-top" style="width: 40%;">
                            <div class="qr-box">
                                <img src="{{ $qr_erp }}" alt="QR ERP" style="width: 110px; height: 110px;">
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
@endif