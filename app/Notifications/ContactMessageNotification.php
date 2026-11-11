<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $fullName,
        public string $email,
        public string $phone,
        public string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('NOUVEAU MESSAGE — Formulaire de contact OCD LIVE')
            ->greeting('NOUVEAU MESSAGE DE CONTACT')
            ->line('**E-mail :** '.$this->email);

        if ($this->fullName !== '') {
            $mail->line('**Nom :** '.$this->fullName);
        }

        if ($this->phone !== '') {
            $mail->line('**Téléphone :** '.$this->phone);
        }

        return $mail->line('**Message :**')->line($this->message);
    }
}
