<?php

namespace App\Notifications;

use App\Enums\ParticipationMode;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification
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

        $mail = (new MailMessage)
            ->subject('Réservation confirmée — OCD Live '.$b->reference)
            ->greeting('Félicitations '.$b->customer->first_name.' !')
            ->line('Votre passage sur OCD Live est **confirmé**.')
            ->line('**Référence :** '.$b->reference)
            ->line('**Business :** '.$b->business->name)
            ->line('**Package :** '.$b->package->name.' ('.$b->package->duration_text.')')
            ->line('**Date :** '.date_fr($b->starts_at))
            ->line('**Heure :** '.$b->time_range.' (heure de Lomé)');

        if ($local = $b->participant_time_range) {
            $mail->line('**Dans votre fuseau :** '.$local);
        }

        if ($b->mode === ParticipationMode::Onsite) {
            $mail->line('Merci de vous présenter au studio de Lomé 30 minutes avant le début de l\'émission avec vos produits.')
                ->line('Adresse : '.setting('contact_address'));
        } else {
            $mail->line('Notre assistante basée à Lomé vous contactera sur WhatsApp pour préparer la connexion et faire un test technique avant l\'émission.');
        }

        return $mail->salutation('L\'équipe OCD Live');
    }
}
