@php
    $logoUrl = 'https://app.portafolioerp.com/img/logo_contabilidad.png';
    if (!empty($empresa->logo)) {
        $logo = $empresa->logo;
        if (filter_var($logo, FILTER_VALIDATE_URL) || str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
            $logoUrl = $logo;
        } else {
            $logoUrl = 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/' . ltrim($logo, '/');
        }
    }

    $fmt = fn ($fecha) => $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : '—';

    $dias = null;
    if (!empty($factura->fecha_vencimiento)) {
        $vence = \Carbon\Carbon::parse($factura->fecha_vencimiento)->startOfDay();
        $dias  = (int) now()->startOfDay()->diffInDays($vence, false);
    }

    // ===== Estado de pago real (pagado / pendiente) =====
    $estaPagada = (isset($estado) && strtolower($estado) === 'pagado');

    if ($estaPagada) {
        $estadoTxt = 'Pagada';
        $estadoBg  = '#dcfce7';
        $estadoFg  = '#15803d';
    } elseif ($dias === null) {
        $estadoTxt = 'Pendiente de pago'; $estadoBg = '#eef2ff'; $estadoFg = '#4f46e5';
    } elseif ($dias < 0) {
        $estadoTxt = 'Vencida hace ' . abs($dias) . ($dias === -1 ? ' día' : ' días'); $estadoBg = '#fee2e2'; $estadoFg = '#b91c1c';
    } elseif ($dias === 0) {
        $estadoTxt = 'Vence hoy'; $estadoBg = '#fef3c7'; $estadoFg = '#b45309';
    } else {
        $estadoTxt = 'Vence en ' . $dias . ($dias === 1 ? ' día' : ' días'); $estadoBg = '#eef2ff'; $estadoFg = '#4f46e5';
    }

    $nombreEmpresa = $empresa->nombre ?? 'Portafolio ERP';
    $valorFactura  = number_format($factura->total_factura, 2, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>Factura {{ $factura->documento_referencia_fe }}</title>
    <style>
        body { margin: 0; padding: 0; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table { border-collapse: collapse; mso-table-lspace: 0; mso-table-rspace: 0; }
        img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        a { text-decoration: none; }

        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; }
            .px { padding-left: 20px !important; padding-right: 20px !important; }
            .amount { font-size: 30px !important; }
            .h1 { font-size: 24px !important; }
            .stack { display: block !important; width: 100% !important; text-align: left !important; }
            .stack-pad { padding-top: 2px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#eef1f8;">

    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;font-size:1px;line-height:1px;">
        Factura {{ $factura->documento_referencia_fe }} de {{ $nombreEmpresa }} · Valor ${{ $valorFactura }} · {{ $estadoTxt }}
        &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f8;">
        <tr>
            <td align="center" style="padding:32px 12px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" class="container" style="width:600px;max-width:600px;">

                    {{-- ============ CABECERA ============ --}}
                    <tr>
                        <td align="center" bgcolor="#4f46e5" style="background-color:#4f46e5;background-image:linear-gradient(135deg,#312e81 0%,#4f46e5 55%,#6366f1 100%);border-radius:20px 20px 0 0;padding:36px 24px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:14px;">
                                        <img src="{{ $logoUrl }}" alt="{{ $nombreEmpresa }}"
                                             style="display:block;max-height:64px;max-width:200px;background:#ffffff;border-radius:14px;padding:8px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:700;letter-spacing:3px;color:#c7d2fe;text-transform:uppercase;">
                                        {{ $nombreEmpresa }}
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" class="h1" style="padding-top:10px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:28px;line-height:1.2;font-weight:800;color:#ffffff;">
                                        Factura electr&oacute;nica
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Línea de acento cian --}}
                    <tr>
                        <td height="4" bgcolor="#22d3ee" style="height:4px;line-height:4px;font-size:0;background-color:#22d3ee;background-image:linear-gradient(90deg,#6366f1,#22d3ee);">&nbsp;</td>
                    </tr>

                    {{-- ============ SALUDO ============ --}}
                    <tr>
                        <td class="px" bgcolor="#ffffff" style="background-color:#ffffff;padding:36px 40px 12px;">
                            <p style="margin:0 0 14px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#1e293b;">
                                Hola <strong>{{ $cliente->nombre_completo }}</strong>,
                            </p>
                            <p style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.7;color:#475569;">
                                Te enviamos los detalles de tu factura para tu revisi&oacute;n.
                            </p>
                        </td>
                    </tr>

                    {{-- ============ N° DE FACTURA + ESTADO ============ --}}
                    <tr>
                        <td class="px" bgcolor="#ffffff" style="background-color:#ffffff;padding:22px 40px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="stack" valign="middle" style="font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                                        <div style="font-size:11px;font-weight:700;letter-spacing:1.5px;color:#94a3b8;text-transform:uppercase;">Documento</div>
                                        <div style="font-family:'Courier New',Consolas,monospace;font-size:20px;font-weight:800;color:#1e293b;line-height:1.4;">
                                            {{ $factura->documento_referencia_fe }}
                                        </div>
                                    </td>
                                    <td class="stack stack-pad" align="right" valign="middle">
                                        <span style="display:inline-block;padding:7px 14px;border-radius:999px;background-color:{{ $estadoBg }};color:{{ $estadoFg }};font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:12px;font-weight:800;letter-spacing:.3px;">
                                            &#9679; {{ $estadoTxt }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ============ DETALLE ============ --}}
                    <tr>
                        <td class="px" bgcolor="#ffffff" style="background-color:#ffffff;padding:14px 40px 6px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                {{-- Fecha de emisión: SIEMPRE visible --}}
                                <tr>
                                    <td style="padding:13px 0;border-bottom:1px dashed #e3e8f4;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#64748b;">Fecha de emisi&oacute;n</td>
                                    <td align="right" style="padding:13px 0;border-bottom:1px dashed #e3e8f4;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;font-weight:700;color:#1e293b;">{{ $fmt($factura->fecha_manual) }}</td>
                                </tr>

                                {{-- Fecha de vencimiento: solo si NO está pagada --}}
                                @if (!$estaPagada)
                                    <tr>
                                        <td style="padding:13px 0;border-bottom:1px dashed #e3e8f4;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#64748b;">Fecha de vencimiento</td>
                                        <td align="right" style="padding:13px 0;border-bottom:1px dashed #e3e8f4;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;font-weight:700;color:#1e293b;">{{ $fmt($factura->fecha_vencimiento) }}</td>
                                    </tr>
                                @endif

                                {{-- Estado de pago: SIEMPRE visible --}}
                                <tr>
                                    <td style="padding:13px 0;{{ !$estaPagada ? 'border-bottom:1px dashed #e3e8f4;' : '' }}font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#64748b;">Estado de pago</td>
                                    <td align="right" style="padding:13px 0;{{ !$estaPagada ? 'border-bottom:1px dashed #e3e8f4;' : '' }}">
                                        <span style="display:inline-block;padding:4px 12px;border-radius:999px;background-color:{{ $estadoBg }};color:{{ $estadoFg }};font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:12px;font-weight:800;">
                                            {{ $estaPagada ? '✓ Pagada' : 'Pendiente' }}
                                        </span>
                                    </td>
                                </tr>

                                {{-- Plazo: solo si NO está pagada y hay fecha de vencimiento --}}
                                @if (!$estaPagada && $dias !== null)
                                    <tr>
                                        <td style="padding:13px 0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#64748b;">Plazo</td>
                                        <td align="right" style="padding:13px 0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;font-weight:800;color:{{ $estadoFg }};">{{ $estadoTxt }}</td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    {{-- ============ VALOR DE LA FACTURA ============ --}}
                    <tr>
                        <td class="px" bgcolor="#ffffff" style="background-color:#ffffff;padding:14px 40px 8px;">
                            {{-- Contenedor exterior: aplica border-radius + fondo + border a través de tabla anidada --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="border-collapse:separate;border-spacing:0;border-radius:16px;background-color:{{ $estaPagada ? '#dcfce7' : '#eef2ff' }};background-image:linear-gradient(135deg,{{ $estaPagada ? '#dcfce7' : '#eef2ff' }} 0%,{{ $estaPagada ? '#bbf7d0' : '#e0f7fc' }} 100%);border:1px solid {{ $estaPagada ? '#86efac' : '#c7d2fe' }};">
                                <tr>
                                    <td align="center"
                                        style="border-radius:16px;padding:22px 16px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                                        <div style="font-size:11px;font-weight:800;letter-spacing:2px;color:{{ $estaPagada ? '#15803d' : '#6366f1' }};text-transform:uppercase;">
                                            Valor de la factura
                                        </div>
                                        <div class="amount" style="margin-top:6px;font-size:36px;line-height:1.15;font-weight:800;letter-spacing:-1px;color:{{ $estaPagada ? '#166534' : '#312e81' }};">
                                            ${{ $valorFactura }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ============ BOTONES ============ --}}
                    @if ((isset($urlPago) || isset($urlPdf) || isset($urlAcuse)))
                        <tr>
                            <td class="px" align="center" bgcolor="#ffffff" style="background-color:#ffffff;padding:14px 40px 4px;">
                                @isset($urlPago)
                                    <a href="{{ $urlPago }}" target="_blank"
                                    style="display:inline-block;margin:4px;padding:14px 28px;border-radius:12px;background-color:#4f46e5;background-image:linear-gradient(120deg,#4f46e5,#6366f1);font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;font-weight:800;color:#ffffff;">
                                        Pagar ahora &rarr;
                                    </a>
                                @endisset

                                @isset($urlPdf)
                                    <a href="{{ $urlPdf }}" target="_blank"
                                    style="display:inline-block;margin:4px;padding:12px 24px;border-radius:12px;border:1.5px solid #c7d2fe;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;font-weight:700;color:#4f46e5;">
                                        Ver factura en PDF
                                    </a>
                                @endisset

                                @if (!$factura->fe_estado_acuse)
                                    @isset($urlAcuse)
                                        <a href="{{ $urlAcuse }}" target="_blank"
                                        style="display:inline-block;margin:4px;padding:14px 28px;border-radius:12px;background-color:#059669;background-image:linear-gradient(120deg,#059669,#10b981);font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;font-weight:800;color:#ffffff;">
                                            ✓ Acuse de recibo
                                        </a>
                                    @endisset
                                @endif
                            </td>
                        </tr>
                    @endif

                    {{-- ============ CONTACTO / EMITIDO POR / NOTAS ============ --}}
                    <tr>
                        <td class="px" bgcolor="#ffffff" style="background-color:#ffffff;padding:26px 40px 36px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td bgcolor="#f5f7fd" style="background-color:#f5f7fd;border-left:4px solid #6366f1;border-radius:10px;padding:16px 18px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;line-height:1.7;color:#64748b;">
                                        @include('emails.texts.contact')

                                        <div style="margin-top:14px;">
                                            @include('emails.texts.emittedBy')
                                        </div>

                                        <div style="margin-top:12px;font-size:12px;">
                                            @include('emails.texts.att')
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:18px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:11px;line-height:1.6;color:#94a3b8;">
                                @include('emails.texts.unsubscribe')
                            </div>
                        </td>
                    </tr>

                    {{-- ============ PIE ============ --}}
                    <tr>
                        <td class="px" align="center" bgcolor="#f8fafc" style="background-color:#f8fafc;border-radius:0 0 20px 20px;padding:22px 24px;">
                            <p style="margin:0 0 6px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:12px;line-height:1.6;color:#94a3b8;">
                                Facturaci&oacute;n electr&oacute;nica · Contabilidad · N&oacute;mina · POS · M&aacute;ximo PH
                            </p>
                            <p style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:12px;line-height:1.6;color:#94a3b8;">
                                &copy; {{ date('Y') }} {{ $nombreEmpresa }} &middot; Medell&iacute;n, Colombia<br>
                                <a href="https://portafolioerp.com" style="color:#6366f1;font-weight:600;">portafolioerp.com</a>
                                &nbsp;&middot;&nbsp;
                                <a href="https://wa.me/573332475846" style="color:#6366f1;font-weight:600;">Soporte por WhatsApp</a>
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>