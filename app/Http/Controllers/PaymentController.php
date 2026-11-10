<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\OnlinePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class PaymentController extends Controller
{
    /** Page de paiement d'une réservation, accessible par son jeton (jamais par son id). */
    public function show(string $token): View
    {
        $booking = $this->booking($token);

        return view('public.payment', ['booking' => $booking]);
    }

    /** Retour du client depuis la page FedaPay : le statut est relu auprès de l'API, pas lu dans l'URL. */
    public function return(string $token, OnlinePaymentService $payments): RedirectResponse
    {
        $booking = $this->booking($token);
        $status = $payments->syncBooking($booking);

        $booking->refresh();

        return redirect()->route('payment.show', $token)->with('payment_status', match (true) {
            $booking->isPaid() => 'paid',
            in_array($status, OnlinePaymentService::FAILED, true) => 'failed',
            default => 'pending',
        });
    }

    /**
     * Webhook FedaPay. La signature est vérifiée, puis la transaction est relue auprès de l'API :
     * le contenu du webhook ne suffit jamais à marquer un paiement comme payé.
     */
    public function webhook(Request $request, OnlinePaymentService $payments): JsonResponse
    {
        $payload = $request->getContent();

        if (! $this->validSignature($payload, (string) $request->header('X-FEDAPAY-SIGNATURE'))) {
            Log::warning('FedaPay : webhook à la signature invalide', ['ip' => $request->ip()]);

            return response()->json(['message' => 'Signature invalide.'], 400);
        }

        $event = json_decode($payload, true) ?: [];
        $transactionId = (int) ($event['entity']['id'] ?? $event['data']['id'] ?? 0);

        if (! str_starts_with((string) ($event['name'] ?? ''), 'transaction.') || ! $transactionId) {
            return response()->json(['message' => 'Événement ignoré.']);
        }

        $payment = $payments->findByTransaction($transactionId);

        if (! $payment) {
            return response()->json(['message' => 'Transaction inconnue.']);
        }

        try {
            $payments->sync($payment, $transactionId);
        } catch (Throwable $e) {
            Log::error('FedaPay : traitement du webhook impossible', ['transaction' => $transactionId, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Erreur temporaire.'], 503); // FedaPay réessaiera
        }

        return response()->json(['message' => 'OK']);
    }

    private function booking(string $token): Booking
    {
        abort_unless(strlen($token) >= 32, 404);

        return Booking::query()->with(['customer', 'package', 'payments'])->where('payment_token', $token)->firstOrFail();
    }

    /** En-tête « t=<timestamp>,s=<signature> » : HMAC-SHA256 de « <timestamp>.<corps> », tolérance 5 min. */
    private function validSignature(string $payload, string $header): bool
    {
        $secret = config('payments.fedapay.webhook_secret');

        if (! $secret || $header === '') {
            return false;
        }

        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 's') {
                $signatures[] = $value;
            }
        }

        if (! $timestamp || ! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach ($signatures as $signature) {
            if (is_string($signature) && hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }
}
