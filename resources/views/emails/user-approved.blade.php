<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cuenta Aprobada - MultiRCV</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">MultiRCV</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
        <h2 style="color: #4F46E5;">¡Tu cuenta ha sido aprobada!</h2>

        <p>Hola <strong>{{ $user->name }}</strong>,</p>

        <p>Nos complace informarte que tu cuenta en MultiRCV ha sido aprobada por un administrador.</p>

        <p>Ya puedes iniciar sesión y comenzar a usar el sistema para gestionar tus registros de compra y venta.</p>

        <div style="margin: 30px 0; text-align: center;">
            <a href="{{ config('app.url') }}/login"
               style="background-color: #4F46E5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Iniciar Sesión
            </a>
        </div>

        <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>

        <p style="margin-top: 30px;">
            Saludos,<br>
            <strong>El equipo de MultiRCV</strong>
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
    </div>
</body>
</html>
