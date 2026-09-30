<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>

    @include('excel.layouts.partials.header', [
        'nombre_empresa' => $nombre_empresa,
        'logo_empresa' => $logo_empresa,
        'nombre_informe' => $nombre_informe,
        'filtros' => $filtros,
        'usuario' => $usuario ?? 'Sistema',
    ])

    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
        <thead>
            <tr>
                @for ($i = 0; $i < count($cuentas); $i++)
                    <th style="background-color: #001c41; color: #ffffff; font-weight: bold; padding: 6px 4px; border: 1px solid #34495e; text-align: left;">
                        {{ $cuentas[$i] }}
                    </th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($detalles as $detalle)
                @php
                    $esTotal = trim($detalle->nombre_nit) === 'TOTAL';
                    $estiloTd = $esTotal
                        ? 'background-color: #1c4587; color: #ffffff; font-weight: bold;'
                        : '';
                    $tieneHora = !\Illuminate\Support\Str::contains($detalle->fecha_manual, '00:00:00');
                    $fechaFormateada = \Carbon\Carbon::parse($detalle->fecha_manual)->format('d/m/Y');
                @endphp
                <tr>
                    <td style="{{ $estiloTd }} padding: 4px; border: 1px solid #ddd;">{{ $detalle->numero_documento }}</td>

                    @if ($tipo_informe == 'resumen_general')
                        <td style="{{ $estiloTd }} padding: 4px; border: 1px solid #ddd;">{{ $detalle->nombre_nit }}</td>
                        <td style="{{ $estiloTd }} padding: 4px; border: 1px solid #ddd;">{{ $detalle->ubicacion }}</td>
                        @for ($i = 1; $i <= (count($cuentas) - 5); $i++)
                            <td style="text-align: right; {{ $estiloTd }} padding: 4px; border: 1px solid #ddd;">
                                {{ number_format($detalle->{'cuenta_' . $i}) ?? 0 }}
                            </td>
                        @endfor
                    @else
                        @for ($i = 1; $i <= (count($cuentas) - 4); $i++)
                            <td style="text-align: right; {{ $estiloTd }} padding: 4px; border: 1px solid #ddd;">
                                {{ number_format($detalle->{'cuenta_' . $i}) ?? 0 }}
                            </td>
                        @endfor
                    @endif

                    @if ($tipo_informe != 'resumen_general')
                        <td style="{{ $estiloTd }} padding: 4px; border: 1px solid #ddd;">{{ number_format($detalle->total_abono) }}</td>
                        <td style="{{ $estiloTd }} padding: 4px; border: 1px solid #ddd;">
                            @if(!$esTotal)
                                {{ $fechaFormateada }}
                            @endif
                        </td>
                    @endif

                    <td style="text-align: right; {{ $estiloTd }} padding: 4px; border: 1px solid #ddd;">
                        {{ number_format($detalle->saldo_final) }}
                    </td>

                    @if ($tipo_informe == 'resumen_general')
                        <td style="{{ $estiloTd }} padding: 4px; border: 1px solid #ddd;">{{ $detalle->dias_mora }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    @include('excel.layouts.partials.footer', [
        'fecha_generacion' => \Carbon\Carbon::now()->format('Y-m-d H:i'),
    ])

</body>
</html>