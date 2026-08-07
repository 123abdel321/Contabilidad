<div class="box" style="width: 100%;">
    <table class="box-table" style="margin-left: auto; width: 100%;">
        <tr class="resumen-title">
            <td colspan="2">{{ $resumen['titulo'] ?? 'RESUMEN' }}</td>
        </tr>
        @foreach ($resumen['filas'] as $filaResumen)
            @php
                $valorResumen = $filaResumen['value'] ?? 0;
                if (isset($filaResumen['formatter']) && $filaResumen['formatter'] == 'number') {
                    $valorResumen = number_format($valorResumen);
                }
                $claseTr = $filaResumen['class'] ?? 'box-body';
            @endphp
            <tr class="{{ $claseTr }}">
                <td>{{ $filaResumen['label'] }}</td>
                <td class="valor">{{ $valorResumen }}</td>
            </tr>
        @endforeach
    </table>
</div>