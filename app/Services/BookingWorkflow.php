<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\AdminActivityLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Transitions de statut d'une réservation, déclenchées par l'administration.
 * Aucune transition n'est automatique : un paiement est enregistré par un humain
 * (ou, plus tard, par un prestataire de paiement réel via webhook vérifié).
 */
class BookingWorkflow
{
    public function recordPayment(Booking $booking, string $method, ?string $reference, ?string $note = null): Payment
    {
        if (in_array($booking->status, [BookingStatus::Cancelled, BookingStatus::Completed], true)) {
            throw ValidationException::withMessages(['payment' => 'Impossible d\'enregistrer un paiement sur une réservation annulée ou terminée.']);
        }

        return DB::transaction(function () use ($booking, $method, $reference, $note) {
            $payment = $booking->payments()->where('status', PaymentStatus::Pending->value)->first()
                ?? $booking->payments()->make(['amount' => $booking->amount, 'currency' => $booking->currency]);

            $payment->fill([
                'provider' => 'manual',
                'method' => $method,
                'status' => PaymentStatus::Paid,
                'provider_reference' => $reference,
                'paid_at' => now(),
                'recorded_by' => auth()->id(),
                'meta' => $note ? ['note' => $note] : null,
            ])->save();

            if ($booking->status === BookingStatus::PendingPayment) {
                $booking->update(['status' => BookingStatus::PendingReview]);
            }

            AdminActivityLog::record('payment.recorded', $booking, ['method' => $method, 'reference' => $reference, 'amount' => $payment->amount]);

            return $payment;
        });
    }

    public function markPaymentFailed(Booking $booking, ?string $note = null): void
    {
        $payment = $booking->payments()->where('status', PaymentStatus::Pending->value)->first();

        if (! $payment) {
            throw ValidationException::withMessages(['payment' => 'Aucun paiement en attente pour cette réservation.']);
        }

        $payment->update(['status' => PaymentStatus::Failed, 'meta' => $note ? ['note' => $note] : null, 'recorded_by' => auth()->id()]);
        // Un nouveau paiement en attente est ouvert pour permettre une nouvelle tentative.
        $booking->payments()->create(['provider' => $payment->provider, 'amount' => $booking->amount, 'currency' => $booking->currency, 'status' => PaymentStatus::Pending]);

        AdminActivityLog::record('payment.failed', $booking, ['note' => $note]);
    }

    public function refund(Payment $payment): void
    {
        if ($payment->status !== PaymentStatus::Paid) {
            throw ValidationException::withMessages(['payment' => 'Seul un paiement encaissé peut être remboursé.']);
        }

        $payment->update(['status' => PaymentStatus::Refunded]);
        AdminActivityLog::record('payment.refunded', $payment->booking, ['payment_id' => $payment->id, 'amount' => $payment->amount]);
    }

    public function confirm(Booking $booking): void
    {
        if (! $booking->isPaid()) {
            throw ValidationException::withMessages(['status' => 'La réservation ne peut pas être confirmée tant que le paiement n\'est pas enregistré comme encaissé.']);
        }

        if (! in_array($booking->status, [BookingStatus::PendingReview, BookingStatus::PendingPayment], true)) {
            throw ValidationException::withMessages(['status' => 'Cette réservation ne peut pas être confirmée depuis son statut actuel.']);
        }

        $booking->update(['status' => BookingStatus::Confirmed, 'confirmed_at' => now()]);
        AdminActivityLog::record('booking.confirmed', $booking);

        try {
            $booking->customer->notify(new BookingConfirmedNotification($booking));
        } catch (\Throwable $e) {
            Log::error('E-mail de confirmation non envoyé', ['booking' => $booking->reference, 'error' => $e->getMessage()]);
        }
    }

    public function cancel(Booking $booking, ?string $reason = null): void
    {
        if (in_array($booking->status, [BookingStatus::Cancelled, BookingStatus::Completed], true)) {
            throw ValidationException::withMessages(['status' => 'Cette réservation est déjà clôturée.']);
        }

        $booking->update(['status' => BookingStatus::Cancelled, 'cancelled_at' => now(), 'cancellation_reason' => $reason]);
        $booking->payments()->where('status', PaymentStatus::Pending->value)->update(['status' => PaymentStatus::Failed->value]);
        AdminActivityLog::record('booking.cancelled', $booking, ['reason' => $reason]);
    }

    public function complete(Booking $booking): void
    {
        if ($booking->status !== BookingStatus::Confirmed) {
            throw ValidationException::withMessages(['status' => 'Seule une réservation confirmée peut être marquée comme terminée.']);
        }

        $booking->update(['status' => BookingStatus::Completed, 'completed_at' => now()]);
        AdminActivityLog::record('booking.completed', $booking);
    }
}
