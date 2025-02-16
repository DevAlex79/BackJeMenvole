<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UserRegisteredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        Log::info("Notification envoyée à : " . $notifiable->email);

        return (new MailMessage)
            ->subject('Bienvenue sur Je m\'envole')
            ->greeting('Bonjour ' . $notifiable->username . ',')
            ->line('Merci de vous être inscrit sur notre site.')
            ->line('Nous espérons que vous apprécierez votre expérience.')
            ->action('Visitez notre site', url('/'))
            ->salutation('Cordialement, l\'équipe Je m\'envole');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
