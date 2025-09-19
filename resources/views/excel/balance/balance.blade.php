
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <table>
        <tr>
            <td rowspan="3" style="vertical-align: middle; align-items: center; text-align: center;">
                <img src="{{ $logo_empresa }}" width="100" />
            </td>
            <td style="font-size: 25px; font-weight: bold;">{{ $nombre_empresa }}</td>
        </tr>
        <tr>
            <td style="font-size: 20px; font-weight: bold;">{{ $nombre_informe }}</td>
        </tr>
        <tr>
            <td style="font-size: 14px;">Fecha de generación: {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</td>
        </tr>
    </table>

	<table>
		<thead>
            <tr>
                <th>Cuenta</th>
                <th>Nombre Cuenta</th>
                <th>Saldo anterior</th>
                <th>Debito</th>
                <th>Credito</th>
                <th>Saldo final</th>
            </tr>
		</thead>
		<tbody>
		@foreach($balances as $balance)
            <tr>
                @if($balance->cuenta == 'TOTALES')
                    @include('excel.balance.celdas', ['style' => 'background-color: #000000; font-weight: bold; color: #ffffff;', 'balance' => $balance])
                @elseif($balance->auxiliar)
                    @include('excel.balance.celdas', ['style' => 'background-color: #ecf6fa; color: #000;', 'balance' => $balance])
                @elseif($balance->balance)
                    @include('excel.balance.celdas', ['style' => 'background-color: #ffffff; color: #000;', 'balance' => $balance])
                @elseif(strlen($balance->cuenta) == 1)
                    @include('excel.balance.celdas', ['style' => 'background-color: #212329; font-weight: bold; color: #ffffff;', 'balance' => $balance])
                @elseif(strlen($balance->cuenta) == 2)
                    @if ($nivel == 1)
                        @include('excel.balance.celdas', ['style' => 'background-color: #ffffff; color: #000;', 'balance' => $balance])
                    @else
                        @include('excel.balance.celdas', ['style' => 'background-color: #37393e; font-weight: bold; color: #ffffff;', 'balance' => $balance])
                    @endif
                @elseif(strlen($balance->cuenta) == 4)
                    @if ($nivel == 2)
                        @include('excel.balance.celdas', ['style' => 'background-color: #ffffff; color: #000;', 'balance' => $balance])
                    @else
                        @include('excel.balance.celdas', ['style' => 'background-color: #33849e; font-weight: 600; color: #ffffff;', 'balance' => $balance])
                    @endif
                @elseif(strlen($balance->cuenta) == 6)
                    @include('excel.balance.celdas', ['style' => 'background-color: #9bd8e9ff; font-weight: 600; color: #000;', 'balance' => $balance])
                @elseif(!$balance->auxiliar)
                    @include('excel.balance.celdas', ['style' => 'background-color: #e8e9e9; font-weight: 700; color: #000;', 'balance' => $balance])
                @endif
            </tr>
		@endforeach
		</tbody>
	</table>
</html>