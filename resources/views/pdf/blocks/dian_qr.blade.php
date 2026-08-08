@if($qr_dian)
<div class="box" style="width: 100%;">
    <table class="box-table" style="width: 100%;">
        <tr class="box-title-row">
            <td class="icon-cell"><img src="{!! icon('file', '#FFFFFF') !!}" width="12"></td>
            <td class="text-align-left">FACTURACIÓN ELECTRÓNICA DIAN</td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="pago-table" style="width: 100%;">
                    <tr>
                        <td class="aling-top" style="width: 30%;">
                            <div class="qr-box">
                                <img src="{{ $qr_dian }}" alt="QR DIAN" style="width: 100px; height: 100px;">
                            </div>
                        </td>
                        <td class="aling-top" style="width: 70%; font-size: 9px; text-transform: none; line-height: 1.5; padding-left: 10px;">
                            @if(isset($qr_info_dian))
                                <strong>Resolución:</strong><br>
                                {{ $qr_info_dian->resolucion }}<br><br>
                                <strong>Cufe:</strong><br>
                                <span style="word-break: break-all;">{{ $qr_info_dian->cufe }}</span>
                            @else
                                <div class="qr-text">
                                    Código de verificación de la DIAN
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
@endif