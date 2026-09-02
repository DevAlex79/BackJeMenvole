<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail de bienvenue envoyé après une inscription réussie.
 */
class UserRegisteredNotification extends Notification
{
    use Queueable;

    /**
     * Canaux de diffusion.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Représentation e-mail.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Bienvenue sur Je m'envole")
            ->greeting('Bonjour ' . $notifiable->username . ',')
            ->line('Merci de vous être inscrit sur notre site.')
            ->line('Nous espérons que vous apprécierez votre expérience.')
            ->action('Visitez notre site', url('/'))
            ->salutation("Cordialement, l'équipe Je m'envole");
    }
}
