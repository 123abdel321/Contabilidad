
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            /* Estilos compatibles con wkhtmltopdf */
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                margin: 0;
                padding: 10px;
            }
            
            .header-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            
            .header-table td {
                padding: 5px;
                vertical-align: top;
            }
            
            .logo-cell {
                width: 120px;
                text-align: center;
                vertical-align: middle !important;
            }
            
            .data-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10px;
            }
            
            .data-table th {
                background-color: #2c3e50;
                color: white;
                padding: 8px 4px;
                text-align: left;
                border: 1px solid #34495e;
                font-weight: bold;
            }
            
            .data-table td {
                padding: 6px 4px;
                border: 1px solid #ddd;
            }
            
            .empresa-nombre {
                font-size: 18px;
                font-weight: bold;
                color: #2c3e50;
            }
            
            .informe-nombre {
                font-size: 16px;
                font-weight: bold;
                color: #e74c3c;
            }
            
            .fecha-info {
                font-size: 12px;
                color: #7f8c8d;
            }
            
            .filtros {
                font-size: 11px;
                background-color: #ecf0f1;
                padding: 8px;
                border-radius: 4px;
            }
            
            .numero {
                text-align: right;
                font-family: 'Courier New', monospace;
            }
            
            .texto-centro {
                text-align: center;
            }

            .header-table-color { background-color: #001c41; color: white; font-weight: 600; }
            .footer-table-color { background-color: #1c4587; color: white; font-weight: 600; }
        </style>
    </head>

    <body>

        <!-- Encabezado mejorado -->
        <table class="header-table">
            <tr>
                <td class="logo-cell" rowspan="4">
                    <img src="{{ $logo_empresa }}" width="80" style="max-width: 80px;" />
                </td>
                <td class="empresa-nombre">{{ $nombre_empresa }}</td>
            </tr>
            <tr>
                <td class="informe-nombre">{{ $nombre_informe }}</td>
            </tr>
            <tr>
                <td class="fecha-info">
                    <strong>Fecha generación:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }} | 
                    <strong>Usuario:</strong> {{ $usuario ?? 'Sistema' }}
                </td>
            </tr>
            <tr>
                <td>
                    <div class="filtros">
                        <strong>FILTROS APLICADOS:</strong><br>
                        @if ($filtros->id_nit)
                            <strong>Nit:</strong> {{ $filtros->id_nit }}<br>
                        @endif
                        @if ($filtros->fecha_desde)
                            <strong>Fecha desde:</strong> {{ $filtros->fecha_desde }}<br>
                        @endif
                        @if ($filtros->fecha_hasta)
                            <strong>Fecha hasta:</strong> {{ $filtros->fecha_hasta }}<br>
                        @endif                        
                    </div>
                </td>
            </tr>
        </table>

        <!-- Tabla de datos -->
        <table>
            <thead>
            <tr>
                @for ($i = 0; $i < count($cuentas); $i++)
                    <th style="background-color: #001c41; color: white; font-weight: 600;">{{ $cuentas[$i] }}</th>
                @endfor
            </tr>
            </thead>
            <tbody>
                @foreach ($detalles as $detalle)
                    @php
                        $esTotal = trim($detalle->nombre_nit) === 'TOTAL';
                        $estiloTd = $esTotal ? 'background-color: #1c4587; color: white; font-weight: 600;' : '';
                        $tieneHora = !\Illuminate\Support\Str::contains($detalle->fecha_manual, '00:00:00');
                        $fechaFormateada = \Carbon\Carbon::parse($detalle->fecha_manual)->format('d/m/Y');
                    @endphp
                    <tr>
                        {{-- Campos fijos --}}
                        <td style="{{ $estiloTd }}">{{ $detalle->numero_documento }}</td>

                        @if ($tipo_informe == 'resumen_general')
                            <td style="{{ $estiloTd }}">{{ $detalle->nombre_nit }}</td>
                            <td style="{{ $estiloTd }}">{{ $detalle->ubicacion }}</td>
                            @for ($i = 1; $i <= (count($cuentas) - 5); $i++)
                                <td style="text-align: right; {{ $estiloTd }}">{{ number_format($detalle->{'cuenta_' . $i}) ?? 0 }}</td>
                            @endfor
                        @else
                            @for ($i = 1; $i <= (count($cuentas) - 4); $i++)
                                <td style="text-align: right; {{ $estiloTd }}">{{ number_format($detalle->{'cuenta_' . $i}) ?? 0 }}</td>
                            @endfor
                        @endif

                        {{-- Campos finales --}}
                        @if ($tipo_informe != 'resumen_general')
                            <td style="{{ $estiloTd }}">{{ $detalle->total_abono }}</td>
                            <td style="{{ $estiloTd }}">{{ $fechaFormateada }}</td>
                        @endif

                        <td style="text-align: right; {{ $estiloTd }}">{{ number_format($detalle->saldo_final) }}</td>

                        @if ($tipo_informe == 'resumen_general')
                            <td style="{{ $estiloTd }}">{{ $detalle->dias_mora }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>

</html>