<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 18px 22px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
    table { width: 100%; border-collapse: collapse; }
    td, th { vertical-align: middle; }
    .r { text-align: right; } .c { text-align: center; }
    .muted { color: #64748b; }

    /* ===== CABECERA ===== */
    .top { background: #312e81; border-radius: 14px 14px 0 0; }
    .top td { padding: 12px 16px; }
    .logo-box { background: #ffffff; border-radius: 10px; padding: 6px; }
    .top-emp { font-size: 9px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #c7d2fe; }
    .top-title { font-size: 20px; font-weight: bold; color: #ffffff; margin-top: 3px; }
    .top-per { font-size: 9px; color: #c7d2fe; margin-top: 3px; }
    .accent { height: 4px; background: #22d3ee; }

    /* ===== EMPLEADO ===== */
    .emp-card { background: #f5f7fd; border-left: 4px solid #6366f1; }
    .emp-card td { padding: 10px 12px; }
    .avatar-img { width: 54px; height: 54px; border: 2px solid #c7d2fe; border-radius: 10px; }
    .avatar-ini { width: 54px; height: 54px; line-height: 54px; background: #e0e7ff; border-radius: 10px;
                  color: #312e81; font-size: 20px; font-weight: bold; text-align: center; }
    .eyebrow { font-size: 8px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; color: #94a3b8; }
    .emp-name { font-size: 15px; font-weight: bold; color: #1e293b; margin: 2px 0 4px; }
    .emp-meta { font-size: 9px; color: #64748b; }
    .pill { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 9px; font-weight: bold; }
    .pill-pend { background: #fef3c7; color: #b45309; }
    .pill-caus { background: #eef2ff; color: #4f46e5; }
    .pill-pag  { background: #dcfce7; color: #15803d; }

    /* ===== FILAS PUNTEADAS ===== */
    .rows td { padding: 6px 0; border-bottom: 1px dashed #e3e8f4; }
    .rows .l { font-size: 9px; font-weight: bold; color: #64748b; }
    .rows .v { font-size: 10px; font-weight: bold; color: #1e293b; text-align: right; }

    /* ===== KPIs ===== */
    .kpis { border-collapse: separate; border-spacing: 6px 0; margin-left: -6px; width: 103%; }
    .kpi { padding: 7px 10px; width: 33%; border-radius: 10px; }
    .kpi-in  { background: #ecfdf3; border: 1px solid #abefc6; }
    .kpi-out { background: #fef3f2; border: 1px solid #fecdca; }
    .kpi-l { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #64748b; }
    .kpi-v { font-size: 14px; font-weight: bold; }
    .kpi-s { font-size: 8px; color: #64748b; }

    .bar td { height: 6px; padding: 0; }

    /* ===== DETALLE ===== */
    .detalle th { background: #4f46e5; color: #fff; font-size: 8px; letter-spacing: 1px; text-transform: uppercase; padding: 5px 8px; }
    .detalle td { padding: 4px 8px; border-bottom: 1px dashed #e3e8f4; }
    .sec-in  td { background: #ecfdf3; color: #067647; font-weight: bold; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; }
    .sec-out td { background: #fef3f2; color: #b42318; font-weight: bold; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; }
    .sub td { background: #f5f7fd; font-weight: bold; border-bottom: 1px solid #c7d2fe; }
    .obs { font-size: 8px; color: #64748b; }

    /* ===== NOTAS + TOTALES ===== */
    .nota { background: #f5f7fd; border-left: 4px solid #6366f1; padding: 8px 10px; font-size: 9px; line-height: 1.5; color: #64748b; }
    .nota b { color: #312e81; letter-spacing: 1px; text-transform: uppercase; font-size: 8px; }
    .sum { border: 1px solid #c7d2fe; }
    .sum td { padding: 5px 10px; border-bottom: 1px dashed #e3e8f4; }
    .sum tr.neto td { background: #312e81; color: #fff; font-size: 12px; font-weight: bold; padding: 8px 10px; border-bottom: 0; }

    .firma { border-top: 1px solid #98a2b3; text-align: center; padding-top: 4px; font-size: 9px; color: #64748b; }
    .foot { background: #f8fafc; border-radius: 0 0 14px 14px; padding: 8px; font-size: 8px; color: #94a3b8; text-align: center; }
</style>
</head>
@php
    $m = fn ($v) => number_format((float) $v, 2, ',', '.');
    $pctNeto = max(0, min(100, 100 - $totales['pct_ded']));

    $pillClase = match ($periodo['estado']) {
        'Pagado'  => 'pill-pag',
        'Causado' => 'pill-caus',
        default   => 'pill-pend',
    };
@endphp
<body>

{{-- ============ CABECERA ============ --}}
<table class="top">
    <tr>
        @if ($empresa->logo)
            <td style="width:110px;">
                <div class="logo-box">
                    <img src="{{ $bucket . ltrim($empresa->logo, '/') }}" style="max-height:44px; max-width:100px;">
                </div>
            </td>
        @endif
        <td>
            <div class="top-emp">{{ $empresa->razon_social }}</div>
            <div class="top-title">Comprobante de nómina</div>
            <div class="top-per">{{ $periodo['inicio'] }} al {{ $periodo['fin'] }}</div>
        </td>
        <td class="r" style="width:150px; font-size:9px; color:#c7d2fe;">
            NIT: {{ $empresa->nit }}{{ $empresa->dv ? '-' . $empresa->dv : '' }}<br>
            @if($empresa->telefono) Tel: {{ $empresa->telefono }}<br>@endif
            @if($empresa->email) {{ $empresa->email }} @endif
        </td>
    </tr>
</table>
<div class="accent"></div>

<div style="height:8px;"></div>

{{-- ============ EMPLEADO + DATOS DEL PERIODO (2 columnas) ============ --}}
<table>
    <tr>
        {{-- Columna izquierda: empleado --}}
        <td style="width:58%; vertical-align:top; padding-right:8px;">
            <table class="emp-card">
                <tr>
                    <td style="width:66px; padding:8px 6px 8px 10px;">
                        @if ($empleado['foto'])
                            <img src="{{ $empleado['foto'] }}" class="avatar-img">
                        @else
                            <div class="avatar-ini">{{ $empleado['iniciales'] }}</div>
                        @endif
                    </td>
                    <td style="padding:8px 10px 8px 4px;">
                        <div class="eyebrow">Desprendible de nómina</div>
                        <div class="emp-name">{{ $empleado['nombre'] }}</div>
                        <div class="emp-meta">
                            {{ $empleado['documento'] }}
                            @if($empleado['email'])<br>{{ $empleado['email'] }}@endif
                            @if($empleado['telefono'])<br>{{ $empleado['telefono'] }}@endif
                        </div>
                    </td>
                </tr>
            </table>
        </td>

        {{-- Columna derecha: periodo / contrato --}}
        <td style="width:42%; vertical-align:top;">
            <table class="rows">
                <tr>
                    <td class="l">Estado</td>
                    <td class="v">
                        @if($periodo['estado'])
                            <span class="pill {{ $pillClase }}">&#9679; {{ $periodo['estado'] }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="l">Periodo</td>
                    <td class="v">{{ $periodo['inicio'] }} al {{ $periodo['fin'] }}</td>
                </tr>
                <tr>
                    <td class="l">Salario base</td>
                    <td class="v">{{ $contrato['salario'] ? '$' . $m($contrato['salario']) : '—' }}</td>
                </tr>
                <tr>
                    <td class="l">Inicio de contrato</td>
                    <td class="v">{{ $contrato['inicio'] ?: '—' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div style="height:8px;"></div>

{{-- ============ NETO + DEVENGADO + DEDUCCIONES (una fila) ============ --}}
<table class="kpis" style="margin-top:8px;">
    <tr>
        <td class="kpi kpi-in">
            <div class="kpi-l">Devengado</div>
            <div class="kpi-v" style="color:#067647;">${{ $m($totales['devengado']) }}</div>
        </td>
        <td class="kpi kpi-out">
            <div class="kpi-l">Deducciones</div>
            <div class="kpi-v" style="color:#b42318;">${{ $m($totales['deduccion']) }}</div>
            <div class="kpi-s">{{ $totales['pct_ded'] }}% del devengado</div>
        </td>
        <td class="kpi" style="background:#eef2ff; border:1px solid #c7d2fe;">
            <div class="kpi-l" style="color:#6366f1;">Neto a pagar</div>
            <div class="kpi-v" style="color:#312e81;">${{ $m($totales['neto']) }}</div>
        </td>
    </tr>
</table>

<table class="bar" style="margin:6px 0 10px;">
    <tr>
        <td style="width:{{ $pctNeto }}%; background:#6366f1;"></td>
        @if($totales['pct_ded'] > 0)
            <td style="width:{{ min(100, $totales['pct_ded']) }}%; background:#f04438;"></td>
        @endif
    </tr>
</table>

{{-- ============ DETALLE ============ --}}
<table class="detalle">
    <thead>
        <tr>
            <th style="width:42%; text-align:left;">Concepto</th>
            <th style="width:12%;" class="c">Cantidad</th>
            <th style="width:6%;" class="c">%</th>
            <th style="width:20%; text-align:left;">Observación</th>
            <th style="width:20%;" class="r">Valor</th>
        </tr>
    </thead>
    <tbody>
        @if(count($devengados))
            <tr class="sec-in"><td colspan="5">Devengados</td></tr>
            @foreach($devengados as $f)
                <tr>
                    <td>{{ $f['concepto'] }}</td>
                    <td class="c">{{ $f['cantidad'] }}</td>
                    <td class="c">{{ $f['porcentaje'] }}</td>
                    <td class="obs">{{ $f['observacion'] }}</td>
                    <td class="r">${{ $m($f['valor']) }}</td>
                </tr>
            @endforeach
            <tr class="sub">
                <td colspan="4" class="r">Subtotal devengados</td>
                <td class="r" style="color:#067647;">${{ $m($totales['devengado']) }}</td>
            </tr>
        @endif

        @if(count($deducciones))
            <tr class="sec-out"><td colspan="5">Deducciones</td></tr>
            @foreach($deducciones as $f)
                <tr>
                    <td>{{ $f['concepto'] }}</td>
                    <td class="c">{{ $f['cantidad'] }}</td>
                    <td class="c">{{ $f['porcentaje'] }}</td>
                    <td class="obs">{{ $f['observacion'] }}</td>
                    <td class="r">-${{ $m($f['valor']) }}</td>
                </tr>
            @endforeach
            <tr class="sub">
                <td colspan="4" class="r">Subtotal deducciones</td>
                <td class="r" style="color:#b42318;">-${{ $m($totales['deduccion']) }}</td>
            </tr>
        @endif
    </tbody>
</table>

<div style="height:8px;"></div>

{{-- ============ NOTAS + TOTALES ============ --}}
<table>
    <tr>
        <td style="width:55%; vertical-align:top; padding-right:10px;">
            <div class="nota">
                <b>Notas</b><br>
                {!! nl2br(e($observacion)) !!}
            </div>
        </td>
        <td style="width:45%; vertical-align:top;">
            <table class="sum">
                <tr><td>Total devengado</td><td class="r">${{ $m($totales['devengado']) }}</td></tr>
                <tr><td>Total deducciones</td><td class="r">-${{ $m($totales['deduccion']) }}</td></tr>
                <tr class="neto"><td>Neto a pagar</td><td class="r">${{ $m($totales['neto']) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

{{-- ============ FIRMAS ============ --}}
<table style="margin-top:26px;">
    <tr>
        <td style="width:8%;"></td>
        <td class="firma" style="width:38%;">Firma del empleado</td>
        <td style="width:8%;"></td>
        <td class="firma" style="width:38%;">Firma del empleador</td>
        <td style="width:8%;"></td>
    </tr>
</table>

{{-- ============ QR ============ --}}
@if(!empty($qr_erp))
    <table style="margin-top:8px;">
        <tr>
            <td style="width:20%;">
                <img src="{{ $qr_erp }}" style="width:70px;height:70px;">
            </td>
            <td class="muted" style="font-size:8px; vertical-align:middle;">
                Escanea el código para validar este comprobante en Portafolio ERP.<br>
                Generado: {{ $fecha_pdf }}
            </td>
        </tr>
    </table>
@endif

{{-- ============ PIE ============ --}}
<div class="foot" style="margin-top:10px;">
    {{ $empresa->razon_social }} · NIT {{ $empresa->nit }} · Generado {{ $fecha_pdf }}
</div>

</body>
</html>