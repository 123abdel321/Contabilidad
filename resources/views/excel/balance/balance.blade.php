
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table>
		<thead>
            <tr>
                <th>Cuenta</th>
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
                    @include('excel.balance.celdas', ['style' => 'background-color: #1c4587; font-weight: bold;', 'balance' => $balance])
                @elseif($balance->auxiliar)
                    @include('excel.balance.celdas', ['style' => 'background-color: #ecf6fa;', 'balance' => $balance])
                @elseif(strlen($balance->cuenta) == 1)
                    @include('excel.balance.celdas', ['style' => 'background-color: #79bfdf; font-weight: bold;', 'balance' => $balance])
                @elseif(strlen($balance->cuenta) == 2)
                    @include('excel.balance.celdas', ['style' => 'background-color: #9fd1e8; font-weight: bold;', 'balance' => $balance])
                @elseif(strlen($balance->cuenta) == 4)
                    @include('excel.balance.celdas', ['style' => 'background-color: #c5e4f1; font-weight: bold;', 'balance' => $balance])
                @elseif(strlen($balance->cuenta) == 6)
                    @include('excel.balance.celdas', ['style' => 'background-color: #d9edf6; font-weight: bold;', 'balance' => $balance])
                @endif
            </tr>
		@endforeach
		</tbody>
	</table>
</html>