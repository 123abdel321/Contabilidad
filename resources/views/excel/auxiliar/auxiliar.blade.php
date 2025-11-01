<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <table>
        <tr>
            <td rowspan="4" style="vertical-align: middle; align-items: center; text-align: center;">
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
        <tr>
            <td style="font-size: 14px; font-weight: bold;">Filtros aplicados:</td>
        </tr>
        <tr>
            <td></td>
            <td style="font-size: 12px;">
                <strong>Fecha desde:</strong> {{ $auxiliar->fecha_desde ?? 'No especificado' }} | 
                <strong>Fecha hasta:</strong> {{ $auxiliar->fecha_hasta ?? 'No especificado' }} | 
                <strong>Nit:</strong> {{ $auxiliar->nit ? $auxiliar->nit->numero_documento . ' - ' . ($auxiliar->nit->nombre_completo ?? $auxiliar->nit->nombre) : 'Todos los nits' }} | 
                <strong>Cuenta:</strong> {{ $auxiliar->cuenta ? $auxiliar->cuenta->cuenta . ' - ' . ($auxiliar->cuenta->nombre ?? $auxiliar->cuenta->nombre_cuenta) : 'Todas las cuentas' }}
            </td>
        </tr>
    </table>

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
		@foreach($auxiliares as $auxiliarItem)
            <tr>
                @if($auxiliarItem->naturaleza_cuenta == 0 && intval($auxiliarItem->saldo_final) < 0 && $auxiliarItem->detalle_group == 'nits')
                    @php
                        $primerosDos = substr($auxiliarItem->cuenta, 0, 2);
                    @endphp
                    @if($primerosDos != '11')
                        @include('excel.auxiliar.celdas', ['style' => 'background-color: #ff6666; font-weight: bold;', 'auxiliar' => $auxiliarItem])
                    @else
                        @include('excel.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliarItem])
                    @endif
                @elseif($auxiliarItem->naturaleza_cuenta == 1 && intval($auxiliarItem->saldo_final) > 0 && $auxiliarItem->detalle_group == 'nits')
                    @php
                        $primerosDos = substr($auxiliarItem->cuenta, 0, 2);
                    @endphp
                    @if($primerosDos != '11')
                        @include('excel.auxiliar.celdas', ['style' => 'background-color: #ff8585; font-weight: bold;', 'auxiliar' => $auxiliarItem])
                    @else
                        @include('excel.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliarItem])
                    @endif
                @elseif($auxiliarItem->auxiliar)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #FFF;', 'auxiliar' => $auxiliarItem])
                @elseif($auxiliarItem->detalle_group == 'nits-totales')
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #1797c1; font-weight: 600;', 'auxiliar' => $auxiliarItem])
                @elseif($auxiliarItem->detalle_group == 'nits')
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #d4d4d4; font-weight: 500;', 'auxiliar' => $auxiliarItem])
                @elseif($auxiliarItem->cuenta == 'TOTALES')
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #000000; font-weight: bold; color: white;', 'auxiliar' => $auxiliarItem])
                @elseif(strlen($auxiliarItem->cuenta) == 1)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #212329; font-weight: bold; color: white;', 'auxiliar' => $auxiliarItem])
                @elseif(strlen($auxiliarItem->cuenta) == 2)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #292b32; font-weight: bold; color: white;', 'auxiliar' => $auxiliarItem])
                @elseif(strlen($auxiliarItem->cuenta) == 4)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #33849e; font-weight: 600; color: white;', 'auxiliar' => $auxiliarItem])
                @elseif(strlen($auxiliarItem->cuenta) == 6)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #3d9dbd; font-weight: 600; color: white;', 'auxiliar' => $auxiliarItem])
                @elseif(strlen($auxiliarItem->cuenta) > 6)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #48b9df; font-weight: 600; color: white;', 'auxiliar' => $auxiliarItem])
                @elseif($auxiliarItem->detalle == 0 && $auxiliarItem->detalle_group == 0)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #FFFFFF;', 'auxiliar' => $auxiliarItem])
                @elseif($auxiliarItem->detalle_group && !$auxiliarItem->detalle)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #9bd8e9; font-weight: 600;', 'auxiliar' => $auxiliarItem])
                @elseif($auxiliarItem->detalle)
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #9bd8e9; font-weight: 600;', 'auxiliar' => $auxiliarItem])
                @else
                    @include('excel.auxiliar.celdas', ['style' => 'background-color: #FFFFFF;', 'auxiliar' => $auxiliarItem])
                @endif
            </tr>
        @endforeach
		</tbody>
	</table>
</html>