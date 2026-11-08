<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Accusé de réception envoyé au client : la demande est enregistrée, pas encore confirmée. */
class BookingReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $b = $this->booking;

        return (new MailMessage)
            ->subject('Votre demande OCD Live '.$b->reference.' est bien reçue')
            ->greeting('Bonjour '.$b->customer->first_name.',')
            ->line('Nous avons bien reçu votre demande de passage sur OCD Live.')
            ->line('**Référence :** '.$b->reference)
            ->line('**Package :** '.$b->package->name.' — '.money_fcfa($b->amount))
            ->line('**Date :** '.date_fr($b->starts_at).', '.$b->time_range.' (heure de Lomé)')
            ->line('**Mode :** '.$b->mode->label())
            ->line('Votre créneau est réservé provisoirement. Votre réservation sera confirmée après réception du paiement et validation par notre équipe.')
            ->line('Notre équipe vous contactera sur WhatsApp au '.$b->customer->whatsapp.' pour les modalités de paiement.')
            ->salutation('L\'équipe OCD Live');
    }
}
