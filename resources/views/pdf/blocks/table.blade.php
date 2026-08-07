<table class="box-table" style="width: 100%;">
    <tbody class="detalle-factura">
        <tr class="box-title-header" style="background-color: #003165;">
            <td colspan="{{ count($tabla['columns']) }}" class="text-align-left">
                {{ $tabla['title'] ?? 'DETALLE' }}
            </td>
        </tr>
        <tr class="box-title-row">
            @foreach ($tabla['columns'] as $columna)
                <td style="text-align: {{ $columna['align'] ?? 'left' }}; 
                        width: {{ $columna['width'] ?? 'auto' }}; 
                        padding-left:10px; 
                        padding-right:10px;">
                    {{ $columna['label'] }}
                </td>
            @endforeach
        </tr>
        @foreach ($tabla['rows'] as $fila)
            <tr class="border-solid">
                @foreach ($tabla['columns'] as $columna)
                    @php
                        $valor = $fila[$columna['key']] ?? '';
                        if (isset($columna['formatter']) && $columna['formatter'] == 'number') {
                            $valor = number_format($valor);
                        }
                    @endphp
                    <td style="text-align: {{ $columna['align'] ?? 'left' }}; 
                            padding-left:10px; 
                            padding-right:10px;">
                        {{ $valor }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>