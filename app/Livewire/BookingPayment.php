<?php

namespace App\Livewire;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Services\OnlinePaymentService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Choix du mode de paiement et règlement d'une réservation.
 * Utilisé à la fin du formulaire de réservation et sur la page /paiement/{token}.
 */
class BookingPayment extends Component
{
    #[Locked]
    public string $token;

    public ?string $method = null;

    public string $phone = '';

    /** choose | waiting | manual */
    public string $stage = 'choose';

    public function mount(string $token): void
    {
        $this->token = $token;
        $booking = $this->booking();
        $this->phone = $this->localNumber($booking->customer->phone);

        $pending = $booking->payments->firstWhere('status', PaymentStatus::Pending);

        if ($pending && in_array($pending->method, ['transfer', 'cash'], true)) {
            $this->method = $pending->method;
            $this->stage = 'manual';
        } elseif ($pending && isset($pending->meta['sent_at'])) {
            $this->method = $pending->method;
            $this->stage = 'waiting';
        }
    }

    public function choose(string $method): void
    {
        $this->resetErrorBag();
        $this->method = $method;
        $this->stage = 'choose';
    }

    /** Valide le mode choisi : demande sur le téléphone, redirection carte ou instructions. */
    public function pay(OnlinePaymentService $payments): void
    {
        $booking = $this->booking();

        if (! $payments->isPayable($booking)) {
            return;
        }

        $method = $payments->methods()[$this->method] ?? null;
        if (! $method) {
            throw ValidationException::withMessages(['method' => 'Choisissez un mode de paiement.']);
        }

        $key = 'booking-pay:'.$booking->id;
        if ($method['type'] !== 'manual' && RateLimiter::tooManyAttempts($key, 6)) {
            throw ValidationException::withMessages(['method' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($key).' secondes.']);
        }

        match ($method['type']) {
            'mobile' => $this->payByMobile($payments, $booking, $key),
            'card' => $this->redirectTo($payments->checkoutUrl($booking, $this->method), $key),
            default => $this->chooseManual($payments, $booking),
        };
    }

    /** Repli : payer le Mobile Money via la page sécurisée FedaPay. */
    public function payOnFedaPayPage(OnlinePaymentService $payments): void
    {
        $booking = $this->booking();

        if ($payments->isPayable($booking) && $this->method) {
            $this->redirectTo($payments->checkoutUrl($booking, $this->method), 'booking-pay:'.$booking->id);
        }
    }

    /** Interrogé toutes les 5 s pendant l'attente de validation sur le téléphone. */
    public function refreshStatus(OnlinePaymentService $payments): void
    {
        $booking = $this->booking();
        $status = $payments->syncBooking($booking);

        if (in_array($status, OnlinePaymentService::FAILED, true)) {
            $this->stage = 'choose';
            $this->addError('phone', 'Le paiement a été refusé ou annulé sur le téléphone. Vous pouvez réessayer ou choisir un autre mode.');
        }
    }

    public function changeMethod(): void
    {
        $this->resetErrorBag();
        $this->stage = 'choose';
    }

    private function payByMobile(OnlinePaymentService $payments, Booking $booking, string $key): void
    {
        $this->validate(['phone' => ['required', 'string', 'max:30']], [], ['phone' => 'numéro Mobile Money']);
        RateLimiter::hit($key, 900);
        $payments->startMobile($booking, $this->method, $this->phone);
        $this->stage = 'waiting';
    }

    private function redirectTo(string $url, string $key): void
    {
        RateLimiter::hit($key, 900);
        $this->redirect($url);
    }

    private function chooseManual(OnlinePaymentService $payments, Booking $booking): void
    {
        $payments->chooseManual($booking, $this->method);
        $this->stage = 'manual';
    }

    private function booking(): Booking
    {
        return Booking::query()->with(['customer', 'package', 'payments'])->where('payment_token', $this->token)->firstOrFail();
    }

    private function localNumber(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return str_starts_with($digits, '228') && strlen($digits) === 11 ? substr($digits, 3) : (string) $phone;
    }

    public function render()
    {
        $payments = app(OnlinePaymentService::class);
        $booking = $this->booking();

        return view('livewire.booking-payment', [
            'booking' => $booking,
            'methods' => $payments->methods(),
            'onlineAvailable' => $payments->onlineAvailable(),
            'payable' => $payments->isPayable($booking),
            'paid' => $booking->isPaid(),
        ]);
    }
}
