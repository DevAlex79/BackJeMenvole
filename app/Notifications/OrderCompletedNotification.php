<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompletedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $orderDetails)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                ->subject('Commande Finalisée')
                ->greeting('Bonjour ' . $notifiable->name . ',')
                ->line('Votre commande a bien été finalisée.')
                ->line('Détails de votre commande :')
                ->line('Numéro : ' . $this->orderDetails['order_id'])
                ->line('Montant : ' . $this->orderDetails['amount'] . '€')
                ->action('Voir mes commandes', url('/orders'))
                ->salutation('Merci pour votre confiance, l\'équipe Je m\'envole');
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
