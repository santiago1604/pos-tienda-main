<?php

namespace App\Mail;

use App\Models\Repair;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo enviado al cliente cuando su reparación está lista para retirar.
 *
 * Uso en el controlador:
 *   Mail::to($customerEmail)->send(new RepairEntregadaMail($repair));
 *
 * Configurar en .env:
 *   MAIL_MAILER=smtp
 *   MAIL_HOST=smtp.gmail.com
 *   MAIL_PORT=587
 *   MAIL_USERNAME=tu@correo.com
 *   MAIL_PASSWORD=app_password
 *   MAIL_FROM_ADDRESS=notificaciones@storecell.com
 *   MAIL_FROM_NAME="StoreCell"
 */
class RepairEntregadaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Repair $repair
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "✅ Tu equipo está listo — StoreCell #{$this->repair->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.repair_entregada',
            with: [
                'repair'       => $this->repair,
                'storeName'    => \App\Models\Setting::get('store_name', 'StoreCell'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
