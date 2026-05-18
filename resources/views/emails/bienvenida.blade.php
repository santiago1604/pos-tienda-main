<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bienvenido a {{ $storeName }}</title>
  <style>
    body        { margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif; color:#333; }
    .wrapper    { max-width:600px; margin:40px auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
    .header     { background:#1a1a2e; padding:28px 32px; text-align:center; }
    .header h1  { margin:0; color:#ffffff; font-size:22px; }
    .body       { padding:32px; }
    .creds      { background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:20px 24px; margin:24px 0; }
    .creds p    { margin:6px 0; font-size:14px; }
    .creds .label { color:#6b7280; }
    .creds .value { font-weight:bold; font-family:monospace; font-size:15px; }
    .badge      { display:inline-block; padding:4px 12px; border-radius:12px; font-size:12px; font-weight:bold; }
    .badge-admin { background:#fee2e2; color:#991b1b; }
    .badge-seller { background:#dbeafe; color:#1e40af; }
    .badge-technician { background:#fef3c7; color:#92400e; }
    .cta        { background:#1a1a2e; color:#ffffff !important; text-decoration:none; display:inline-block; padding:12px 28px; border-radius:6px; margin-top:16px; font-size:14px; }
    .warn       { font-size:12px; color:#dc2626; margin-top:16px; }
    .footer     { background:#f9f9f9; padding:16px 32px; text-align:center; font-size:12px; color:#9ca3af; border-top:1px solid #eee; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>{{ $storeName }}</h1>
    </div>
    <div class="body">
      <p>Hola <strong>{{ $user->name }}</strong>,</p>
      <p>
        Se ha creado una cuenta para ti en el sistema de punto de venta <strong>{{ $storeName }}</strong>.
        Tu rol asignado es:
        <span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
      </p>

      <div class="creds">
        <p><span class="label">Correo electrónico:</span><br><span class="value">{{ $user->email }}</span></p>
        <p><span class="label">Contraseña temporal:</span><br><span class="value">{{ $plainPassword }}</span></p>
      </div>

      <a href="{{ $loginUrl }}" class="cta">Iniciar sesión →</a>

      <p class="warn">
        ⚠️ Por seguridad, cambia tu contraseña después del primer inicio de sesión.
        No compartas estas credenciales con nadie.
      </p>
    </div>
    <div class="footer">
      Este correo fue enviado automáticamente por {{ $storeName }}.
    </div>
  </div>
</body>
</html>
