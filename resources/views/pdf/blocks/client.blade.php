@if($cliente)
    <div class="box" style="width: 100%;">
        <table class="box-table" style="width: 100%;">
            <tr class="box-title-row">
                <td class="icon-cell"><img src="{!! icon('user', '#FFFFFF') !!}" width="12"></td>
                <td colspan="2" class="text-align-left">{{ $cliente->titulo }}</td>
            </tr>
            <tr>
                <td colspan="3" class="box-body-title">{{ $cliente->nombre_cliente }}</td>
            </tr>
            @foreach ($cliente->datos_adicionales as $dato)
                @if ($dato->valor)
                <tr class="box-body">
                    <td class="icon"><img src="{!! icon($dato->icono, '#003165') !!}" width="10"></td>
                    <td class="lbl">{{ $dato->titulo }}</td>
                    <td class="val">{{ $dato->valor }}</td>
                </tr>
                @endif
            @endforeach
            <tr><td colspan="2" class="spacer-sm"></td></tr>
        </table>
    </div>
@endif