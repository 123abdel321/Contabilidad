<div class="header-gastos-titulo">{{ $titulo }}</div>
<div class="spacer-sm"></div>
<div class="header-gastos-consecutivo">{{ $consecutivo }}</div>
<div class="spacer-sm"></div>
<table style="border: none; width: auto; margin: 0 auto;">
    <tr>
        <td style="border:none; padding-right: 5px;">
            <img src="{!! icon('calendar') !!}" width="16">
        </td>
        <td style="border:none; text-align: left;">
            <div class="header-gastos-fecha-label">Fecha de expedición</div>
            <div class="header-gastos-fecha-valor">{{ $fecha }}</div>
        </td>
    </tr>
</table>