<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifica tu cuenta</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden;">
        <div style="background: #4f46e5; color: white; padding: 30px; text-align: center;">
            <h1 style="margin: 0;">Portafolio ERP</h1>
            <p style="margin: 5px 0 0;">Verifica tu cuenta</p>
        </div>
        <div style="padding: 30px;">
            <p>Hola <strong>{{ $nombre_completo }}</strong>,</p>
            <p>Gracias por registrarte. Usa este código para verificar tu cuenta:</p>
            <div style="text-align: center; margin: 30px 0;">
                <div style="display: inline-block; background: #f0f0f0; padding: 15px 30px; border-radius: 8px; font-size: 32px; letter-spacing: 8px; font-weight: bold; color: #4f46e5;">
                    {{ $codigo }}
                </div>
            </div>
            <p style="color: #666; font-size: 13px;">Si no solicitaste este código, ignora este correo.</p>
        </div>
        <div style="background: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #888;">
            © {{ date('Y') }} Portafolio ERP
        </div>
    </div>
</body>
</html>