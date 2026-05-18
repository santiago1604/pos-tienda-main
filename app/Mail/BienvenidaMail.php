<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de bienvenida enviado al crear un usuario nuevo.
 *
 * Uso en AdminUserController:
 *   Mail::to($user->email)->send(new BienvenidaMail($user, $plainPassword));
 */
class BienvenidaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $plainPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '👋 Bienvenido a StoreCell — Tus credenciales de acceso',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida',
            with: [
                'user'          => $this->user,
                'plainPassword' => $this->plainPassword,
                'storeName'     => \App\Models\Setting::get('store_name', 'StoreCell'),
                'loginUrl'      => url('/login'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
