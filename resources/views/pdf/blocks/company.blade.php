<table>
    <tr>
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
        <td class="header-empresa-datos" style="width: 30%; padding-left: 12px;">
            <table class="header-tabla-interna text-align-left">
                <tbody>
                    <tr><td style="width: 16px;"><img src="{!! icon('building') !!}" width="10"></td><td>NIT: {{ $empresa->nit }}{{ $empresa->dv ? ' - '.$empresa->dv : '' }}</td></tr>
                    @if($empresa->telefono)<tr><td><img src="{!! icon('phone') !!}" width="11"></td><td>{{ $empresa->telefono }}</td></tr>@endif
                    @if($empresa->email)<tr><td><img src="{!! icon('mail') !!}" width="11"></td><td>{{ $empresa->email }}</td></tr>@endif
                    @if($empresa->direccion)<tr><td><img src="{!! icon('location') !!}" width="11"></td><td>{{ $empresa->direccion }}</td></tr>@endif
                </tbody>
            </table>
        </td>
    </tr>
</table>