<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de Soporte - MultiRCV</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #DC2626; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
        <h1 style="margin: 0;">MultiRCV - Soporte</h1>
        <p style="margin: 5px 0 0 0; font-size: 14px;">Nuevo Ticket de Ayuda</p>
    </div>

    <div style="background-color: #f9f9f9; padding: 30px; border-radius: 0 0 5px 5px;">
        <h2 style="color: #DC2626; margin-top: 0;">{{ $ticketSubject }}</h2>

        <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #DC2626; font-size: 16px;">Información del Usuario</h3>

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">Nombre:</td>
                    <td style="padding: 8px 0;">{{ $user->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Email:</td>
                    <td style="padding: 8px 0;"><a href="mailto:{{ $user->email }}" style="color: #DC2626;">{{ $user->email }}</a></td>
                </tr>
                @if($user->phone)
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Teléfono:</td>
                    <td style="padding: 8px 0;">{{ $user->phone }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Fecha:</td>
                    <td style="padding: 8px 0;">{{ now()->format('d/m/Y H:i:s') }}</td>
                </tr>
            </table>
        </div>

        <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #DC2626;">
            <h3 style="margin-top: 0; color: #DC2626; font-size: 16px;">Mensaje</h3>
            <div style="white-space: pre-wrap; color: #333; line-height: 1.6;">{{ $ticketMessage }}</div>
        </div>

        <div style="background-color: #FEF2F2; border-left: 4px solid #DC2626; padding: 15px; margin: 20px 0; border-radius: 3px;">
            <p style="margin: 0; color: #991B1B; font-size: 12px;">
                <strong>Nota:</strong> Puedes responder directamente a este correo para contactar al usuario.
            </p>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px; color: #666; font-size: 12px;">
        <p>Este correo fue enviado automáticamente desde MultiRCV.</p>
        <p style="margin-top: 10px;">© {{ date('Y') }} MultiRCV - Sistema de Gestión de RCV</p>
    </div>
</body>
</html>
