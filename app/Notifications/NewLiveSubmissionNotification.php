<?php

namespace App\Notifications;

use App\Models\LiveSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLiveSubmissionNotification extends Notification
{
    use Queueable;

    public function __construct(public LiveSubmission $submission, public bool $mailOnly = false) {}

    public function via(object $notifiable): array
    {
        return $this->mailOnly ? ['mail'] : ['database', 'mail'];
    }

    /** Lignes du message, partagées entre l'e-mail et le dashboard. */
    public function lines(): array
    {
        $s = $this->submission;

        return [
            'Référence' => $s->reference,
            'Vendeur' => $s->seller->full_name,
            'Business' => $s->seller->business_name,
            'Pays' => $s->country->name,
            'Ville' => $s->city->name,
            'Catégorie' => $s->category->name,
            'Compte TikTok' => $s->account_name,
            'Créneau souhaité' => $s->schedule?->label,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('NOUVELLE DEMANDE OCD LIVE MARKET — '.$this->submission->reference)
            ->greeting('Nouvelle demande à modérer');

        foreach ($this->lines() as $label => $value) {
            $mail->line("**$label :** ".$value);
        }

        return $mail
            ->line('Statut : en attente de vérification.')
            ->action('Examiner la demande', route('admin.market.submissions.show', $this->submission));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nouvelle demande Market '.$this->submission->reference,
            'submission_id' => $this->submission->id,
            'reference' => $this->submission->reference,
            'lines' => $this->lines(),
        ];
    }
}
