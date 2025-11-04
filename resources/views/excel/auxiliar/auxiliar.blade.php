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
        
        /* Colores para diferentes niveles */
        .nivel-0 { background-color: #000000; color: white; font-weight: bold; }
        .nivel-1 { background-color: #2c3e50; color: white; font-weight: bold; }
        .nivel-2 { background-color: #34495e; color: white; font-weight: bold; }
        .nivel-4 { background-color: #2980b9; color: white; font-weight: 600; }
        .nivel-6 { background-color: #3498db; color: white; font-weight: 600; }
        .nivel-8 { background-color: #5dade2; color: white; font-weight: 600; }
        
        .grupo-nits { background-color: #d4d4d4; font-weight: 500; }
        .grupo-totales { background-color: #1797c1; font-weight: 600; color: white; }
        .total-final { background-color: #000000; font-weight: bold; color: white; }
        
        /* Estados de saldo */
        .saldo-alerta { background-color: #ff6666; font-weight: bold; }
        .saldo-normal { background-color: #ffffff; }
        
        .numero {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .texto-centro {
            text-align: center;
        }
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
                    <strong>Fecha:</strong> {{ $auxiliar->fecha_desde ?? 'No especificado' }} al {{ $auxiliar->fecha_hasta ?? 'No especificado' }} | 
                    <strong>Nit:</strong> {{ $auxiliar->nit ? $auxiliar->nit->numero_documento . ' - ' . ($auxiliar->nit->nombre_completo ?? $auxiliar->nit->nombre) : 'Todos' }} | 
                    <strong>Cuenta:</strong> {{ $auxiliar->cuenta ? $auxiliar->cuenta->cuenta . ' - ' . ($auxiliar->cuenta->nombre ?? $auxiliar->cuenta->nombre_cuenta) : 'Todas' }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabla de datos -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="12%">Cuenta</th>
                <th width="15%">Nit</th>
                <th width="10%">Centro Costos</th>
                <th width="8%">Doc. Ref.</th>
                <th width="8%" class="numero">Saldo Anterior</th>
                <th width="8%" class="numero">Débito</th>
                <th width="8%" class="numero">Crédito</th>
                <th width="8%" class="numero">Saldo Final</th>
                <th width="8%">Comprobante</th>
                <th width="6%">Consecutivo</th>
                <th width="7%">Fecha</th>
                <th width="12%">Concepto</th>
            </tr>
        </thead>
        <tbody>
        @foreach($auxiliares as $auxiliarItem)
            @php
                $claseFila = 'saldo-normal';
                $claseNumero = 'numero';
                
                // Determinar la clase según el tipo de registro
                if($auxiliarItem->detalle_group == 'nits-totales') {
                    $claseFila = 'grupo-totales';
                } elseif($auxiliarItem->detalle_group == 'nits') {
                    $claseFila = 'grupo-nits';
                } elseif($auxiliarItem->cuenta == 'TOTALES') {
                    $claseFila = 'total-final';
                } elseif(strlen($auxiliarItem->cuenta) == 1) {
                    $claseFila = 'nivel-0';
                } elseif(strlen($auxiliarItem->cuenta) == 2) {
                    $claseFila = 'nivel-1';
                } elseif(strlen($auxiliarItem->cuenta) == 4) {
                    $claseFila = 'nivel-4';
                } elseif(strlen($auxiliarItem->cuenta) == 6) {
                    $claseFila = 'nivel-6';
                } elseif(strlen($auxiliarItem->cuenta) > 6) {
                    $claseFila = 'nivel-8';
                }
                
                // Validación de saldos problemáticos
                if($auxiliarItem->naturaleza_cuenta == 0 && intval($auxiliarItem->saldo_final) < 0 && $auxiliarItem->detalle_group == 'nits') {
                    $primerosDos = substr($auxiliarItem->cuenta, 0, 2);
                    if($primerosDos != '11') {
                        $claseFila = 'saldo-alerta';
                    }
                } elseif($auxiliarItem->naturaleza_cuenta == 1 && intval($auxiliarItem->saldo_final) > 0 && $auxiliarItem->detalle_group == 'nits') {
                    $primerosDos = substr($auxiliarItem->cuenta, 0, 2);
                    if($primerosDos != '11') {
                        $claseFila = 'saldo-alerta';
                    }
                }
            @endphp
            
            <tr class="{{ $claseFila }}">
                <td>{{ $auxiliarItem->cuenta }} 
                    @if($auxiliarItem->nombre_cuenta && $auxiliarItem->cuenta != 'TOTALES')
                    - {{ $auxiliarItem->nombre_cuenta }}
                    @endif
                </td>
                <td>
                    @if($auxiliarItem->numero_documento)
                        {{ $auxiliarItem->numero_documento }} - 
                        {{ $auxiliarItem->razon_social ?? $auxiliarItem->nombre_nit ?? '' }}
                    @endif
                </td>
                <td class="texto-centro">
                    @if($auxiliarItem->codigo_cecos)
                        {{ $auxiliarItem->codigo_cecos }}
                    @endif
                </td>
                <td class="texto-centro">{{ $auxiliarItem->documento_referencia }}</td>
                <td class="{{ $claseNumero }}">{{ number_format($auxiliarItem->saldo_anterior, 2) }}</td>
                <td class="{{ $claseNumero }}">{{ number_format($auxiliarItem->debito, 2) }}</td>
                <td class="{{ $claseNumero }}">{{ number_format($auxiliarItem->credito, 2) }}</td>
                <td class="{{ $claseNumero }}">{{ number_format($auxiliarItem->saldo_final, 2) }}</td>
                <td class="texto-centro">
                    @if($auxiliarItem->codigo_comprobante)
                        {{ $auxiliarItem->codigo_comprobante }}
                    @endif
                </td>
                <td class="texto-centro">{{ $auxiliarItem->consecutivo }}</td>
                <td class="texto-centro">
                    @if($auxiliarItem->fecha_manual)
                        {{ \Carbon\Carbon::parse($auxiliarItem->fecha_manual)->format('d/m/Y') }}
                    @endif
                </td>
                <td>{{ $auxiliarItem->concepto }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Pie de página -->
    <div style="margin-top: 20px; font-size: 10px; color: #7f8c8d; text-align: center; border-top: 1px solid #bdc3c7; padding-top: 10px;">
        Generado el {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }} | 
        Página 1 de 1
    </div>

</body>
</html>