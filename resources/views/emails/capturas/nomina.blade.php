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

    $nombreEmpresa = $empresa->nombre ?? $empresa->razon_social ?? 'Portafolio ERP';
    $valorNomina   = number_format((float) ($valorNeto ?? $documento->credito ?? 0), 2, ',', '.');
    $numDocumento  = $documento->consecutivo ?? '—';
    $periodoTexto  = $fmt($periodo->fecha_inicio_periodo) . ' al ' . $fmt($periodo->fecha_fin_periodo);
    $fechaPago     = $fmt($fechaNomina ?? $documento->fecha_manual);
@endphp
<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>Nómina {{ $numDocumento }}</title>
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
        Nómina {{ $numDocumento }} de {{ $nombreEmpresa }} · Neto ${{ $valorNomina }} · Período {{ $periodoTexto }}
        &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#eef1f8;">
        <tr>
            <td align="center" style="padding:32px 12px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" class="container" style="width:600px;max-width:600px;">

                    {{-- ============ CABECERA ============ --}}
                    <tr>
                        <td align="center" bgcolor="#0f766e" style="background-color:#0f766e;background-image:linear-gradient(135deg,#134e4a 0%,#0f766e 55%,#14b8a6 100%);border-radius:20px 20px 0 0;padding:36px 24px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:14px;">
                                        <img src="{{ $logoUrl }}" alt="{{ $nombreEmpresa }}"
                                             style="display:block;max-height:64px;max-width:200px;background:#ffffff;border-radius:14px;padding:8px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:700;letter-spacing:3px;color:#99f6e4;text-transform:uppercase;">
                                        {{ $nombreEmpresa }}
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" class="h1" style="padding-top:10px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:28px;line-height:1.2;font-weight:800;color:#ffffff;">
                                        Comprobante de n&oacute;mina
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Línea de acento --}}
                    <tr>
                        <td height="4" bgcolor="#22d3ee" style="height:4px;line-height:4px;font-size:0;background-color:#22d3ee;background-image:linear-gradient(90deg,#14b8a6,#22d3ee);">&nbsp;</td>
                    </tr>

                    {{-- ============ SALUDO ============ --}}
                    <tr>
                        <td class="px" bgcolor="#ffffff" style="background-color:#ffffff;padding:36px 40px 12px;">
                            <p style="margin:0 0 14px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#1e293b;">
                                Hola <strong>{{ $empleado->nombre_completo }}</strong>,
                            </p>
                            <p style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.7;color:#475569;">
                                Adjuntamos tu comprobante de n&oacute;mina correspondiente al per&iacute;odo
                                <strong>{{ $periodoTexto }}</strong>.
                            </p>
                        </td>
                    </tr>

                    {{-- ============ N° DOCUMENTO + FECHA ============ --}}
                    <tr>
                        <td class="px" bgcolor="#ffffff" style="background-color:#ffffff;padding:22px 40px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="stack" valign="middle" style="font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                                        <div style="font-size:11px;font-weight:700;letter-spacing:1.5px;color:#94a3b8;text-transform:uppercase;">Documento</div>
                                        <div style="font-family:'Courier New',Consolas,monospace;font-size:20px;font-weight:800;color:#1e293b;line-height:1.4;">
                                            {{ $numDocumento }}
                                        </div>
                                    </td>
                                    <td class="stack stack-pad" align="right" valign="middle">
                                        <span style="display:inline-block;padding:7px 14px;border-radius:999px;background-color:#ccfbf1;color:#0f766e;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:12px;font-weight:800;letter-spacing:.3px;">
                                            &#9679; Aprobada
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
                                <tr>
                                    <td style="padding:13px 0;border-bottom:1px dashed #e3e8f4;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#64748b;">Per&iacute;odo</td>
                                    <td align="right" style="padding:13px 0;border-bottom:1px dashed #e3e8f4;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;font-weight:700;color:#1e293b;">{{ $periodoTexto }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:13px 0;border-bottom:1px dashed #e3e8f4;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#64748b;">Fecha de pago</td>
                                    <td align="right" style="padding:13px 0;border-bottom:1px dashed #e3e8f4;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;font-weight:700;color:#1e293b;">{{ $fechaPago }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:13px 0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;color:#64748b;">Documento empleado</td>
                                    <td align="right" style="padding:13px 0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:14px;font-weight:700;color:#1e293b;">{{ $empleado->numero_documento }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ============ VALOR NETO ============ --}}
                    <tr>
                        <td class="px" bgcolor="#ffffff" style="background-color:#ffffff;padding:14px 40px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="border-collapse:separate;border-spacing:0;border-radius:16px;background-color:#ccfbf1;background-image:linear-gradient(135deg,#ccfbf1 0%,#a7f3d0 100%);border:1px solid #5eead4;">
                                <tr>
                                    <td align="center" style="border-radius:16px;padding:22px 16px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">
                                        <div style="font-size:11px;font-weight:800;letter-spacing:2px;color:#0f766e;text-transform:uppercase;">
                                            Neto a pagar
                                        </div>
                                        <div class="amount" style="margin-top:6px;font-size:36px;line-height:1.15;font-weight:800;letter-spacing:-1px;color:#134e4a;">
                                            ${{ $valorNomina }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ============ CONTACTO / EMITIDO POR / NOTAS ============ --}}
                    <tr>
                        <td class="px" bgcolor="#ffffff" style="background-color:#ffffff;padding:26px 40px 36px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td bgcolor="#f5f7fd" style="background-color:#f5f7fd;border-left:4px solid #14b8a6;border-radius:10px;padding:16px 18px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;line-height:1.7;color:#64748b;">
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
                                <a href="https://portafolioerp.com" style="color:#14b8a6;font-weight:600;">portafolioerp.com</a>
                                &nbsp;&middot;&nbsp;
                                <a href="https://wa.me/573332475846" style="color:#14b8a6;font-weight:600;">Soporte por WhatsApp</a>
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>
</body>
</html>