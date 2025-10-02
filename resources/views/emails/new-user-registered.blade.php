<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nuevo Usuario Registrado - MultiRCV</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">MultiRCV</h1>
        <p style="margin: 5px 0 0 0; font-size: 14px;">Notificación de Registro</p>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
        <h2 style="color: #4F46E5; margin-top: 0;">Nuevo Usuario Registrado</h2>

        <p>Se ha registrado un nuevo usuario en el sistema MultiRCV y está pendiente de aprobación.</p>

        <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #4F46E5; font-size: 16px;">Información del Usuario</h3>

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">Nombre:</td>
                    <td style="padding: 8px 0;">{{ $user->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Email:</td>
                    <td style="padding: 8px 0;">{{ $user->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Teléfono:</td>
                    <td style="padding: 8px 0;">{{ $user->phone }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Es Contador:</td>
                    <td style="padding: 8px 0;">{{ $user->is_accountant ? 'Sí' : 'No' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Fecha de Registro:</td>
                    <td style="padding: 8px 0;">{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
            </table>
        </div>

        <div style="background-color: #D1FAE5; border-left: 4px solid #10B981; padding: 15px; margin: 20px 0; border-radius: 3px;">
            <p style="margin: 0; color: #065F46;">
                <strong>Información:</strong> El usuario ya tiene acceso al sistema y puede comenzar a utilizarlo.
            </p>
        </div>

        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            Este correo es solo informativo. El usuario se registró exitosamente en MultiRCV.
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
        <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
        <p style="margin-top: 10px;">© {{ date('Y') }} MultiRCV - Sistema de Gestión de RCV</p>
    </div>
</body>
</html>
