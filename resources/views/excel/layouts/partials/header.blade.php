<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
    <tr>
        {{-- LOGO --}}
        <td rowspan="4" style="width: 150px; text-align: center; vertical-align: middle; padding: 6px;">
            @if ($logo_empresa)
                <img src="{{ $logo_empresa }}" width="120" />
            @endif
        </td>

        {{-- NOMBRE EMPRESA --}}
        <td style="font-size: 18px; font-weight: bold; color: #001c41; padding: 4px 8px;">
            {{ $nombre_empresa }}
        </td>
    </tr>
    <tr>
        {{-- NOMBRE INFORME --}}
        <td style="font-size: 14px; font-weight: bold; color: #e74c3c; padding: 4px 8px;">
            {{ $nombre_informe }}
        </td>
    </tr>
    <tr>
        {{-- FECHA Y USUARIO --}}
        <td style="font-size: 10px; color: #7f8c8d; padding: 4px 8px;">
            <strong>Usuario:</strong> {{ $usuario }}
        </td>
    </tr>
    <tr>
        {{-- FILTROS --}}
        <td style="font-size: 10px; color: #2c3e50; padding: 4px 8px;">
            @if ($filtros)
                @if ($filtros->id_nit) Nit: {{ $filtros->id_nit }} @endif
                @if ($filtros->ubicacion) | Ubicación: {{ $filtros->ubicacion }} @endif
                @if ($filtros->fecha_desde) | Desde: {{ $filtros->fecha_desde }} @endif
                @if ($filtros->fecha_hasta) | Hasta: {{ $filtros->fecha_hasta }} @endif
            @endif
        </td>
    </tr>
</table>