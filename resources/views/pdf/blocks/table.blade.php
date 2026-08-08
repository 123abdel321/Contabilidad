<div class="box">

    <table class="box-table tabla-paginada">

        <thead>

            <tr class="box-title-header">
                <td colspan="{{ count($tabla['columns']) }}" class="text-align-left" style="padding: 8px 10px;">
                    {{ $tabla['title'] }}
                </td>
            </tr>

            <tr class="box-title-row">
                @foreach($tabla['columns'] as $col)
                    <td style="
                        text-align: {{ $col['align'] }};
                        width: {{ $col['width'] ?? 'auto' }};
                        padding-left: 10px;
                        padding-right: 10px;
                    ">
                        {{ $col['label'] }}
                    </td>
                @endforeach
            </tr>

        </thead>

        <tbody class="detalle-factura">

            @foreach($tabla['rows'] as $fila)

                <tr class="border-solid">

                    @foreach($tabla['columns'] as $col)

                        @php
                            $valor = $fila[$col['key']] ?? '';

                            if ($col['formatter'] === 'number') {
                                $valor = number_format($valor);
                            }
                        @endphp

                        <td style="
                            text-align: {{ $col['align'] }};
                            padding-left: 10px;
                            padding-right: 10px;
                        ">
                            {{ $valor }}
                        </td>

                    @endforeach

                </tr>

            @endforeach

        </tbody>

    </table>

</div>