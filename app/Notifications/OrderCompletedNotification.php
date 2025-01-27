<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    // public function __construct(public $orderDetails)
    // {
    //     //
    // }

    public function __construct(public $order)
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
                ->greeting('Bonjour ' . $notifiable->username . ',')
                ->line('Votre commande a bien été finalisée.')
                ->line('Détails de votre commande :')
                ->line('Numéro de commande : ' . $this->order->id_order)
                ->line('Montant total : ' . $this->order->total_price . '€')
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
            'order_id' => $this->order->id,
            'amount' => $this->order->amount,
        ];
    }
}
