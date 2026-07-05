<html>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    @include('excel.base_header', ['encabezado' => $encabezado])

    <table>
        <thead>
            <tr>
                <th>Cuenta</th>
                <th>Nombre Cuenta</th>
                <th>Saldo anterior</th>
                @foreach($mesesMostrar as $mes)
                    <th>{{ ucfirst($mes) }}</th>
                @endforeach
                <th>Saldo final</th>
                <th>Ppto anterior</th>
                <th>Ppto movimiento</th>
                <th>Ppto acumulado</th>
                <th>Ppto diferencia</th>
                <th>Ppto porcentaje</th>
                <th>Ppto porcentaje acumulado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultados as $resultado)
                <tr>
                    @if($resultado->cuenta == 'TOTALES')
                        @include('excel.resultado.celdas', ['style' => 'background-color: #000000; font-weight: bold; color: #ffffff;', 'resultado' => $resultado, 'mesesMostrar' => $mesesMostrar])
                    @elseif($resultado->auxiliar)
                        @include('excel.resultado.celdas', ['style' => 'background-color: #FFF; color: #000000;', 'resultado' => $resultado, 'mesesMostrar' => $mesesMostrar])
                    @elseif(strlen($resultado->cuenta) == 1)
                        @include('excel.resultado.celdas', ['style' => 'background-color: #212329; color: #ffffff; font-weight: bold;', 'resultado' => $resultado, 'mesesMostrar' => $mesesMostrar])
                    @elseif(strlen($resultado->cuenta) == 2)
                        @include('excel.resultado.celdas', ['style' => 'background-color: #4d4f54; color: #ffffff; font-weight: bold;', 'resultado' => $resultado, 'mesesMostrar' => $mesesMostrar])
                    @elseif(strlen($resultado->cuenta) == 4)
                        @include('excel.resultado.celdas', ['style' => 'background-color: #33849e; color: #ffffff; font-weight: 600;', 'resultado' => $resultado, 'mesesMostrar' => $mesesMostrar])
                    @elseif(strlen($resultado->cuenta) == 6)
                        @include('excel.resultado.celdas', ['style' => 'background-color: #9bd8e9ff; font-weight: 600;', 'resultado' => $resultado, 'mesesMostrar' => $mesesMostrar])
                    @else
                        @include('excel.resultado.celdas', ['style' => 'background-color: #FFF; color: #000000;', 'resultado' => $resultado, 'mesesMostrar' => $mesesMostrar])
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</html>