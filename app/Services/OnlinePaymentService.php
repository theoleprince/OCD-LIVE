<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\AdminActivityLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Payments\FedaPayClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * Paiement d'une réservation par le client, après validation de sa demande.
 *  - Mobile Money (Moov Money, Mixx by Yas) : demande de paiement envoyée sur le téléphone
 *    du client (FedaPay sans redirection), puis attente de sa validation.
 *  - Carte : redirection vers la page sécurisée FedaPay.
 *  - Virement / espèces : instructions, enregistrement par l'administration.
 * Le statut d'un paiement en ligne est toujours relu auprès de FedaPay (sync()).
 */
class OnlinePaymentService
{
    public const APPROVED = ['approved', 'transferred'];

    public const FAILED = ['declined', 'canceled', 'cancelled', 'refunded', 'expired'];

    public function __construct(private FedaPayClient $fedapay, private BookingWorkflow $workflow) {}

    public function onlineAvailable(): bool
    {
        return $this->fedapay->isConfigured();
    }

    /** Modes affichés au client, avec leur disponibilité. */
    public function methods(): array
    {
        return collect(config('payments.methods'))
            ->map(fn (array $m, string $key) => $m + [
                'key' => $key,
                'available' => $m['type'] === 'manual' || $this->onlineAvailable(),
            ])
            ->all();
    }

    public function method(string $key): array
    {
        $method = $this->methods()[$key] ?? null;

        if (! $method) {
            throw ValidationException::withMessages(['method' => 'Mode de paiement inconnu.']);
        }
        if (! $method['available']) {
            throw ValidationException::withMessages(['method' => 'Ce mode de paiement n\'est pas encore disponible. Choisissez un autre mode.']);
        }

        return $method;
    }

    public function isPayable(Booking $booking): bool
    {
        return $booking->status === BookingStatus::PendingPayment && ! $booking->isPaid();
    }

    /** Paiement Mobile Money : retourne le paiement en attente de validation sur le téléphone. */
    public function startMobile(Booking $booking, string $methodKey, string $phone): Payment
    {
        $method = $this->method($methodKey);
        if ($method['type'] !== 'mobile') {
            throw ValidationException::withMessages(['method' => 'Mode de paiement invalide.']);
        }

        $number = $this->normalizePhone($phone);
        $payment = $this->openTransaction($booking, $methodKey);

        try {
            $this->fedapay->sendMobilePayment($method['fedapay_mode'], $payment->meta['fedapay_token'], '+'.$number, config('payments.fedapay.country'));
        } catch (RuntimeException $e) {
            Log::warning('FedaPay : échec de la demande de paiement mobile', ['booking' => $booking->reference, 'error' => $e->getMessage()]);

            throw ValidationException::withMessages([
                'phone' => 'La demande de paiement n\'a pas pu être envoyée sur ce numéro. Vérifiez le numéro ou utilisez la page de paiement sécurisée.',
            ]);
        }

        $payment->update(['meta' => array_merge($payment->meta, ['phone' => '+'.$number, 'sent_at' => now()->toIso8601String()])]);

        return $payment;
    }

    /** Paiement par carte (ou repli Mobile Money) : URL de la page sécurisée FedaPay. */
    public function checkoutUrl(Booking $booking, string $methodKey): string
    {
        $method = $this->method($methodKey);
        if ($method['type'] === 'manual') {
            throw ValidationException::withMessages(['method' => 'Mode de paiement invalide.']);
        }

        return $this->openTransaction($booking, $methodKey)->meta['fedapay_url'];
    }

    /** Virement / espèces : mémorise le choix du client pour l'équipe. */
    public function chooseManual(Booking $booking, string $methodKey): void
    {
        $method = $this->method($methodKey);
        if ($method['type'] !== 'manual') {
            throw ValidationException::withMessages(['method' => 'Mode de paiement invalide.']);
        }

        $payment = $this->pendingPayment($booking);
        if ($payment->method !== $methodKey) {
            $payment->update(['method' => $methodKey]);
            AdminActivityLog::record('payment.method_chosen', $booking, ['method' => $methodKey]);
        }
    }

    /**
     * Relit le statut FedaPay d'un paiement et l'applique (payé / échoué).
     * Retourne le statut FedaPay brut (pending, approved, declined…) ou null si non applicable.
     */
    public function sync(Payment $payment, ?int $transactionId = null): ?string
    {
        $current = $payment->meta['fedapay_transaction_id'] ?? null;
        $transactionId ??= $current;

        if (! $transactionId || $payment->status === PaymentStatus::Paid) {
            return null;
        }
        if ($payment->status !== PaymentStatus::Pending && $transactionId === $current) {
            return null;
        }

        $transaction = $this->fedapay->retrieveTransaction((int) $transactionId);
        $status = strtolower((string) ($transaction['status'] ?? 'pending'));
        $isCurrent = (int) $transactionId === (int) $current;

        // Cas rare : une ancienne tentative, rattachée à un paiement déjà clos, est validée après coup.
        // Rien n'est perdu : l'équipe est alertée pour rattacher ou rembourser ce paiement.
        if ($payment->status !== PaymentStatus::Pending) {
            if (in_array($status, self::APPROVED, true)) {
                Log::critical('FedaPay : paiement reçu sur une tentative close', ['payment' => $payment->id, 'transaction' => $transactionId]);
                AdminActivityLog::record('payment.online.paid', $payment->booking, [
                    'method' => $payment->method, 'reference' => $transaction['reference'] ?? null,
                    'amount' => $transaction['amount'] ?? null, 'needs_review' => true,
                ]);
            }

            return $status;
        }

        if (in_array($status, self::APPROVED, true)) {
            if ((int) ($transaction['amount'] ?? 0) < $payment->amount) {
                Log::error('FedaPay : montant payé inférieur au montant dû', ['payment' => $payment->id, 'transaction' => $transactionId]);

                return 'amount_mismatch';
            }

            $this->workflow->recordOnlinePayment($payment, $transaction['reference'] ?? null, ['fedapay_status' => $status, 'fedapay_transaction_id' => (int) $transactionId]);
        } elseif ($isCurrent && in_array($status, self::FAILED, true)) {
            // L'échec d'une ancienne tentative abandonnée n'annule pas la tentative en cours.
            $this->workflow->failOnlinePayment($payment, $status);
        }

        return $status;
    }

    /** Synchronise le paiement FedaPay en cours d'une réservation, s'il y en a un. */
    public function syncBooking(Booking $booking): ?string
    {
        $payment = $booking->payments()->where('status', PaymentStatus::Pending->value)->first();

        if (! $payment) {
            return null;
        }

        try {
            return $this->sync($payment);
        } catch (Throwable $e) {
            Log::warning('FedaPay : synchronisation impossible', ['booking' => $booking->reference, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /** Retrouve le paiement correspondant à une transaction FedaPay (webhook). */
    public function findByTransaction(int $transactionId): ?Payment
    {
        return Payment::query()
            ->where('provider', 'fedapay')
            ->where(fn ($q) => $q->where('meta->fedapay_transaction_id', $transactionId)
                ->orWhereJsonContains('meta->fedapay_transactions', $transactionId))
            ->latest('id')
            ->first();
    }

    /** Crée une transaction FedaPay et son lien de paiement, rattachés au paiement en attente. */
    private function openTransaction(Booking $booking, string $methodKey): Payment
    {
        if (! $this->isPayable($booking)) {
            throw ValidationException::withMessages(['method' => 'Cette réservation n\'est plus en attente de paiement.']);
        }

        $payment = $this->pendingPayment($booking);
        $customer = $booking->customer;

        try {
            $transaction = $this->fedapay->createTransaction([
                'description' => 'OCD Live — réservation '.$booking->reference.' ('.$booking->package->name.')',
                'amount' => $payment->amount,
                'currency' => ['iso' => $payment->currency ?: 'XOF'],
                'callback_url' => route('payment.return', $booking->payment_token),
                'custom_metadata' => ['booking_reference' => $booking->reference, 'payment_id' => $payment->id],
                'customer' => [
                    'firstname' => $customer->first_name,
                    'lastname' => $customer->last_name,
                    'email' => $customer->email,
                ],
            ]);
            $link = $this->fedapay->createToken((int) $transaction['id']);
        } catch (RuntimeException $e) {
            Log::error('FedaPay : création de transaction impossible', ['booking' => $booking->reference, 'error' => $e->getMessage()]);

            throw ValidationException::withMessages(['method' => 'Le service de paiement en ligne est momentanément indisponible. Réessayez dans quelques instants ou choisissez un autre mode.']);
        }

        $payment->update([
            'provider' => 'fedapay',
            'method' => $methodKey,
            'provider_reference' => $transaction['reference'] ?? null,
            'meta' => [
                'fedapay_transaction_id' => (int) $transaction['id'],
                // Historique : une transaction abandonnée puis validée plus tard reste rattachée.
                'fedapay_transactions' => array_values(array_unique([...($payment->meta['fedapay_transactions'] ?? []), (int) $transaction['id']])),
                'fedapay_token' => $link['token'],
                'fedapay_url' => $link['url'],
                'environment' => config('payments.fedapay.environment'),
            ],
        ]);

        AdminActivityLog::record('payment.method_chosen', $booking, ['method' => $methodKey, 'fedapay_transaction' => (int) $transaction['id']]);

        return $payment;
    }

    private function pendingPayment(Booking $booking): Payment
    {
        return $booking->payments()->where('status', PaymentStatus::Pending->value)->first()
            ?? $booking->payments()->create(['provider' => 'manual', 'amount' => $booking->amount, 'currency' => $booking->currency, 'status' => PaymentStatus::Pending]);
    }

    /** Numéro togolais à 8 chiffres, avec ou sans indicatif → 228XXXXXXXX. */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        $dial = config('payments.fedapay.dial_code');

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 8) {
            $digits = $dial.$digits;
        }

        if (! preg_match('/^'.$dial.'\d{8}$/', $digits)) {
            throw ValidationException::withMessages(['phone' => 'Saisissez un numéro Mobile Money togolais à 8 chiffres (ex. 90 12 34 56).']);
        }

        return $digits;
    }
}
