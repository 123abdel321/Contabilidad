
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table>
		<thead>
		<tr>
            <th>Cuenta</th>
            <th>Nit</th>
            <th>Centro costos</th>
            <th>Dcto refe</th>
            <th>Saldo anterior</th>
            <th>Debito</th>
            <th>Credito</th>
            <th>Saldo final</th>
            <th>Comprobante</th>
            <th>Consecutivo</th>
            <th>Fecha</th>
            <th>Concepto</th>
		</tr>
		</thead>
		<tbody>
		@foreach($auxiliares as $auxiliar)
			<tr>
                @if($auxiliar->detalle_group == 'nits-totales')
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: rgb(64 164 209 / 25%); font-weight: bold;', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->detalle_group == 'nits')
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: rgb(64 164 209 / 15%);', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->cuenta == 'TOTALES')
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: rgb(28 69 135); font-weight: bold; color: white;', 'auxiliar' => $auxiliar])
                @elseif(strlen($auxiliar->cuenta) == 1)  
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: rgb(64 164 209 / 90%); font-weight: bold;', 'auxiliar' => $auxiliar])
                @elseif(strlen($auxiliar->cuenta) == 2) 
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: rgb(64 164 209 / 75%); font-weight: bold;', 'auxiliar' => $auxiliar])
                @elseif(strlen($auxiliar->cuenta) == 4) 
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: rgb(64 164 209 / 60%); font-weight: bold;', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->detalle == 0 && $auxiliar->detalle_group == 0) 
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->detalle_group && !$auxiliar->detalle)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: rgb(64 164 209 / 45%); font-weight: bold;', 'auxiliar' => $auxiliar])
                @elseif($auxiliar->detalle)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: rgb(64 164 209 / 35%); font-weight: bold;', 'auxiliar' => $auxiliar])
                @else
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliar])
                @endif
			</tr>
		@endforeach
		</tbody>
	</table>
</html>