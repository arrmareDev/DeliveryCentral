<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class MotorizadoVerifyEmail extends BaseVerifyEmail
{
    /**
     * Genera el enlace firmado apuntando a nuestra propia ruta de API
     * (no a la ruta `verification.verify` por defecto de Laravel, que
     * asume auth de sesión). Este enlace lo abre el correo del usuario
     * directamente — no pasa por el SPA — y el controller redirige al
     * frontend al terminar.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'motorizado.verification.verify',
            now()->addMinutes(60),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ],
        );
    }

    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Confirma tu correo — Delivery Central')
            ->greeting("¡Hola {$notifiable->nombre}!")
            ->line('Confirma tu correo para poder recibir pedidos en Delivery Central.')
            ->action('Confirmar correo', $url)
            ->line('Si no creaste esta cuenta, puedes ignorar este mensaje.');
    }
}
