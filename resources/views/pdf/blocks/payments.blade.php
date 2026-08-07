@if(count($pagos) > 0)
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
                                        @foreach ($pagos as $pago)
                                            <tr>
                                                <td class="nombre-pago">{{ $pago->forma_pago->nombre }}</td>
                                                <td class="valor-pago valor">{{ number_format($pago->valor) }}</td>
                                            </tr>
                                        @endforeach
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
@endif