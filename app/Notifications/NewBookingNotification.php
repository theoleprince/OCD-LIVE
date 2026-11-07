<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking, public bool $mailOnly = false) {}

    public function via(object $notifiable): array
    {
        return $this->mailOnly ? ['mail'] : ['database', 'mail'];
    }

    /** Lignes du message, partagées entre l'e-mail et le dashboard. */
    public function lines(): array
    {
        $b = $this->booking;
        $business = $b->business;
        $socials = collect($business->social_links ?? [])->filter()->map(fn ($v, $k) => ucfirst($k).' : '.$v)->implode(' · ');

        return array_filter([
            'Référence' => $b->reference,
            'Client' => $b->customer->full_name,
            'Pays' => $b->customer->country,
            'Ville' => $b->customer->city,
            'Business' => $business->name.' ('.$business->sector.')',
            'Package' => $b->package->name,
            'Montant' => money_fcfa($b->amount),
            'Mode' => $b->mode->shortLabel(),
            'Date' => date_fr($b->starts_at),
            'Heure' => $b->time_range.' (heure de Lomé)',
            'Téléphone' => $b->customer->phone,
            'WhatsApp' => $b->customer->whatsapp,
            'E-mail' => $b->customer->email,
            'Coordonnées commerciales' => $business->business_phone.' — '.$business->business_address.($socials ? ' — '.$socials : ''),
            'Assistance demandée' => $b->needs_assistance ? 'Oui' : null,
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('NOUVELLE RÉSERVATION OCD LIVE — '.$this->booking->reference)
            ->greeting('NOUVELLE RÉSERVATION OCD LIVE');

        foreach ($this->lines() as $label => $value) {
            $mail->line("**$label :** ".$value);
        }

        return $mail
            ->line('Statut : en attente de paiement.')
            ->action('Ouvrir la réservation', route('admin.bookings.show', $this->booking));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nouvelle réservation '.$this->booking->reference,
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->reference,
            'lines' => $this->lines(),
        ];
    }
}
