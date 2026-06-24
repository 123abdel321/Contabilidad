<style>
    /* Estilos compatibles con Excel */
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
        width: 90px;
        text-align: center;
        vertical-align: middle !important;
        padding-right: 10px;
    }
    
    .logo-cell img {
        width: 80px;
        height: auto;
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
        padding: 10px;
        border-radius: 4px;
    }
    
    .numero {
        text-align: right;
        font-family: 'Courier New', monospace;
    }
    
    .texto-centro {
        text-align: center;
    }
</style>

<!-- Encabezado mejorado -->
<table class="header-table">
    <tr>
        @if($encabezado->logo_empresa)
        <td class="logo-cell" rowspan="4">
            <img src="{{ $encabezado->logo_empresa }}" width="80" />
        </td>
        @endif
        <td class="empresa-nombre" colspan="11">{{ $encabezado->nombre_empresa }}</td>
    </tr>
    <tr>
        <td class="informe-nombre" colspan="11">{{ $encabezado->nombre_informe }}</td>
    </tr>
    <tr>
        <td class="fecha-info" colspan="11">
            <strong>Fecha generación:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}
        </td>
    </tr>
    <tr>
        <td colspan="11">
            <div class="filtros">
                <strong>FILTROS APLICADOS:</strong><br>
                
                @foreach ($encabezado->filtros as $nombre => $valor)
                    @if (!empty($valor))
                        <strong>{{ $nombre }}:&nbsp;</strong> {{ $valor }}<br>
                    @endif
                @endforeach                      
            </div>
        </td>
    </tr>
</table>