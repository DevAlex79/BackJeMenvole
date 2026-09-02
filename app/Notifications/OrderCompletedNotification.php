<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail de confirmation envoyé au client après création d'une commande.
 */
class OrderCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

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
            ->subject('Confirmation de votre commande')
            ->greeting('Bonjour ' . $notifiable->username . ',')
            ->line('Votre commande a bien été enregistrée.')
            ->line('Numéro de commande : ' . $this->order->id_order)
            ->line('Montant total : ' . $this->order->total_price . ' €')
            ->action('Voir mes commandes', url('/orders'))
            ->salutation("Merci pour votre confiance, l'équipe Je m'envole");
    }

    /**
     * Représentation tableau (base de données / broadcast).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id'    => $this->order->id_order,
            'total_price' => $this->order->total_price,
        ];
    }
}
