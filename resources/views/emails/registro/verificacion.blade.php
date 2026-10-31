<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <title>Verifica tu cuenta · Portafolio ERP</title>
    <style>
        body { margin: 0; padding: 0; width: 100% !important; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table { border-collapse: collapse; mso-table-lspace: 0; mso-table-rspace: 0; }
        img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        a { text-decoration: none; }

        @media only screen and (max-width: 620px) {
            .container { width: 100% !important; }
            .px { padding-left: 20px !important; padding-right: 20px !important; }
            .digit { width: 40px !important; height: 52px !important; line-height: 52px !important; font-size: 26px !important; }
            .h1 { font-size: 24px !important; }
        }

        @media (prefers-color-scheme: dark) {
            .bg-page { background-color: #060e26 !important; }
            .bg-card { background-color: #0f1729 !important; border-color: #1e2a52 !important; }
            .txt { color: #e2e8f0 !important; }
            .txt-muted { color: #94a3b8 !important; }
            .digit { background-color: #1b2547 !important; border-color: #3b3f9e !important; color: #a5b4fc !important; }
            .note { background-color: #131c3a !important; border-color: #1e2a52 !important; }
            .bg-foot { background-color: #0a1226 !important; }
        }
    </style>
</head>
<body class="bg-page" style="margin:0;padding:0;background-color:#eef1f8;">

    {{-- Texto de vista previa (lo que se ve junto al asunto en la bandeja) --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;font-size:1px;line-height:1px;">
        Tu código de verificación es {{ $codigo }}. Úsalo para activar tu cuenta en Portafolio ERP.
        &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" class="bg-page" style="background-color:#eef1f8;">
        <tr>
            <td align="center" style="padding:32px 12px;">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" class="container" style="width:600px;max-width:600px;">

                    {{-- ============ CABECERA ============ --}}
                    <tr>
                        <td align="center" bgcolor="#4f46e5"
                            style="background-color:#4f46e5;background-image:linear-gradient(135deg,#312e81 0%,#4f46e5 55%,#6366f1 100%);border-radius:20px 20px 0 0;padding:36px 24px 30px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding-bottom:14px;">
                                        <img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="Portafolio ERP" width="56" height="56"
                                             style="display:block;width:56px;height:56px;border-radius:14px;background:#ffffff;padding:6px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;font-weight:700;letter-spacing:3px;color:#c7d2fe;text-transform:uppercase;">
                                        Portafolio ERP
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" class="h1" style="padding-top:10px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:28px;line-height:1.2;font-weight:800;color:#ffffff;">
                                        Verifica tu cuenta
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Línea de acento cian --}}
                    <tr>
                        <td height="4" bgcolor="#22d3ee" style="height:4px;line-height:4px;font-size:0;background-color:#22d3ee;background-image:linear-gradient(90deg,#6366f1,#22d3ee);">&nbsp;</td>
                    </tr>

                    {{-- ============ CUERPO ============ --}}
                    <tr>
                        <td class="bg-card px" bgcolor="#ffffff"
                            style="background-color:#ffffff;border-left:1px solid #e3e8f4;border-right:1px solid #e3e8f4;padding:36px 40px 12px;">

                            <p class="txt" style="margin:0 0 14px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#1e293b;">
                                Hola <strong>{{ $nombre_completo }}</strong>,
                            </p>
                            <p class="txt-muted" style="margin:0 0 6px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.7;color:#475569;">
                                Gracias por registrarte en Portafolio ERP. Para activar tu cuenta, ingresa este código de verificación:
                            </p>
                        </td>
                    </tr>

                    {{-- ============ CÓDIGO EN CASILLAS ============ --}}
                    <tr>
                        <td class="bg-card px" align="center" bgcolor="#ffffff"
                            style="background-color:#ffffff;border-left:1px solid #e3e8f4;border-right:1px solid #e3e8f4;padding:22px 40px 8px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                                <tr>
                                    @foreach (str_split((string) $codigo) as $digito)
                                        <td style="padding:0 4px;">
                                            <div class="digit" style="width:46px;height:58px;line-height:58px;text-align:center;background-color:#eef2ff;border:1.5px solid #c7d2fe;border-radius:12px;font-family:'Courier New',Consolas,monospace;font-size:30px;font-weight:800;color:#4f46e5;">
                                                {{ $digito }}
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Vigencia (opcional: pasa $minutos desde tu Mailable) --}}
                    <tr>
                        <td class="bg-card px" align="center" bgcolor="#ffffff"
                            style="background-color:#ffffff;border-left:1px solid #e3e8f4;border-right:1px solid #e3e8f4;padding:14px 40px 8px;">
                            <p class="txt-muted" style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;line-height:1.5;color:#64748b;">
                                @isset($minutos)
                                    &#9201; Este código vence en <strong style="color:#4f46e5;">{{ $minutos }} minutos</strong>.
                                @else
                                    &#9201; Este código es de un solo uso y vence pronto.
                                @endisset
                            </p>
                        </td>
                    </tr>

                    {{-- ============ AVISO DE SEGURIDAD ============ --}}
                    <tr>
                        <td class="bg-card px" bgcolor="#ffffff"
                            style="background-color:#ffffff;border-left:1px solid #e3e8f4;border-right:1px solid #e3e8f4;padding:26px 40px 36px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="note" bgcolor="#f5f7fd" style="background-color:#f5f7fd;border:1px solid #e3e8f4;border-left:4px solid #6366f1;border-radius:10px;padding:14px 16px;">
                                        <p class="txt-muted" style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#64748b;">
                                            <strong style="color:#4f46e5;">&#128274; Cuida tu cuenta.</strong>
                                            Nunca compartas este código con nadie. Nuestro equipo jamás te lo pedirá por teléfono ni por WhatsApp.
                                            Si no solicitaste este código, puedes ignorar este correo con tranquilidad.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ============ PIE ============ --}}
                    <tr>
                        <td class="bg-foot px" align="center" bgcolor="#f8fafc"
                            style="background-color:#f8fafc;border:1px solid #e3e8f4;border-top:0;border-radius:0 0 20px 20px;padding:22px 24px;">
                            <p class="txt-muted" style="margin:0 0 6px;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:12px;line-height:1.6;color:#94a3b8;">
                                Facturación electrónica · Contabilidad · Nómina · POS · Máximo PH
                            </p>
                            <p class="txt-muted" style="margin:0;font-family:'Segoe UI',Helvetica,Arial,sans-serif;font-size:12px;line-height:1.6;color:#94a3b8;">
                                &copy; {{ date('Y') }} Portafolio ERP &middot; Medell&iacute;n, Colombia<br>
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