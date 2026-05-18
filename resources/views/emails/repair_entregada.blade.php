<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tu equipo está listo</title>
  <style>
    body        { margin:0; padding:0; background:#f4f4f4; font-family: Arial, sans-serif; color:#333; }
    .wrapper    { max-width:600px; margin:40px auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
    .header     { background:#1a1a2e; padding:28px 32px; text-align:center; }
    .header h1  { margin:0; color:#ffffff; font-size:22px; letter-spacing:.5px; }
    .body       { padding:32px; }
    .badge      { display:inline-block; background:#d1fae5; color:#065f46; border-radius:20px; padding:6px 18px; font-size:14px; font-weight:bold; margin-bottom:20px; }
    .info-table { width:100%; border-collapse:collapse; margin:20px 0; }
    .info-table td { padding:10px 14px; border-bottom:1px solid #f0f0f0; font-size:14px; }
    .info-table td:first-child { color:#6b7280; width:40%; }
    .info-table td:last-child  { font-weight:600; }
    .cta        { background:#1a1a2e; color:#ffffff; text-decoration:none; display:inline-block; padding:12px 28px; border-radius:6px; margin-top:20px; font-size:14px; }
    .footer     { background:#f9f9f9; padding:16px 32px; text-align:center; font-size:12px; color:#9ca3af; border-top:1px solid #eee; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>{{ $storeName }}</h1>
    </div>
    <div class="body">
      <p>Hola <strong>{{ $repair->customer_name }}</strong>,</p>
      <p>¡Buenas noticias! Tu equipo ha sido reparado y está listo para retirar en nuestra tienda.</p>

      <span class="badge">✅ Reparación Completada</span>

      <table class="info-table">
        <tr>
          <td>N.° de reparación</td>
          <td>#{{ $repair->id }}</td>
        </tr>
        <tr>
          <td>Equipo</td>
          <td>{{ $repair->device_description }}</td>
        </tr>
        @if($repair->repair_description)
        <tr>
          <td>Trabajo realizado</td>
          <td>{{ $repair->repair_description }}</td>
        </tr>
        @endif
        @if($repair->total_cost)
        <tr>
          <td>Costo total</td>
          <td>${{ number_format($repair->total_cost, 2) }}</td>
        </tr>
        @endif
        <tr>
          <td>Fecha de entrega</td>
          <td>{{ now()->format('d/m/Y H:i') }}</td>
        </tr>
      </table>

      <p>Por favor acércate con tu número de reparación para retirar tu equipo.</p>

      @if($repair->customer_phone)
      <p style="font-size:13px; color:#6b7280;">
        Si tienes preguntas, puedes llamarnos o escribirnos. Tu número de referencia es <strong>#{{ $repair->id }}</strong>.
      </p>
      @endif
    </div>
    <div class="footer">
      Este correo fue enviado automáticamente por {{ $storeName }}. Por favor no respondas a este mensaje.
    </div>
  </div>
</body>
</html>
