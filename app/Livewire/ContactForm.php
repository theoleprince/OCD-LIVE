<?php

namespace App\Livewire;

use App\Models\User;
use App\Notifications\ContactMessageNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactForm extends Component
{
    public string $fullName = '';

    public string $email = '';

    public string $phone = '';

    public string $message = '';

    public string $website = ''; // pot de miel anti-robot (doit rester vide)

    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'fullName' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function submit(): void
    {
        if ($this->website !== '') {
            abort(422);
        }

        $key = 'contact-submit:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('submit', 'Trop de messages envoyés. Merci de réessayer dans quelques minutes.');

            return;
        }
        RateLimiter::hit($key, 300);

        $this->validate();

        try {
            $admins = User::query()->where('is_active', true)->get();
            Notification::send($admins, new ContactMessageNotification($this->fullName, $this->email, $this->phone, $this->message));

            if ($extra = setting('admin_notification_email')) {
                Notification::route('mail', $extra)->notify(new ContactMessageNotification($this->fullName, $this->email, $this->phone, $this->message));
            }
        } catch (\Throwable $e) {
            Log::error('E-mail de contact non envoyé', ['error' => $e->getMessage()]);
        }

        $this->reset(['fullName', 'email', 'phone', 'message']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
