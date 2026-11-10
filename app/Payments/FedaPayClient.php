<?php

namespace App\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Client minimal de l'API FedaPay (https://docs.fedapay.com).
 * Les réponses peuvent être enveloppées (« v1/transaction ») : unwrap() les normalise.
 */
class FedaPayClient
{
    public function isConfigured(): bool
    {
        return filled(config('payments.fedapay.secret_key'));
    }

    public function baseUrl(): string
    {
        return config('payments.fedapay.environment') === 'live'
            ? 'https://api.fedapay.com/v1'
            : 'https://sandbox-api.fedapay.com/v1';
    }

    /** @return array{id:int, reference:?string, status:string} */
    public function createTransaction(array $payload): array
    {
        return $this->unwrap($this->post('/transactions', $payload));
    }

    /** @return array{token:string, url:string} */
    public function createToken(int $transactionId): array
    {
        $data = $this->post('/transactions/'.$transactionId.'/token');

        if (empty($data['token']) || empty($data['url'])) {
            throw new RuntimeException('Réponse FedaPay inattendue lors de la génération du lien de paiement.');
        }

        return ['token' => $data['token'], 'url' => $data['url']];
    }

    /** Demande de paiement mobile sans redirection : le client valide sur son téléphone. */
    public function sendMobilePayment(string $mode, string $token, string $phoneE164, string $country): array
    {
        return $this->post('/transactions/'.$mode, [
            'token' => $token,
            'phone_number' => ['number' => $phoneE164, 'country' => $country],
        ]);
    }

    public function retrieveTransaction(int $transactionId): array
    {
        try {
            $data = $this->request()->get('/transactions/'.$transactionId)->throw()->json() ?? [];
        } catch (RequestException $e) {
            throw new RuntimeException('FedaPay : '.$this->errorMessage($e), previous: $e);
        }

        return $this->unwrap($data);
    }

    private function post(string $path, array $payload = []): array
    {
        try {
            return $this->request()->post($path, $payload)->throw()->json() ?? [];
        } catch (RequestException $e) {
            throw new RuntimeException('FedaPay : '.$this->errorMessage($e), previous: $e);
        }
    }

    private function request(): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('FedaPay n\'est pas configuré (FEDAPAY_SECRET_KEY manquante).');
        }

        return Http::baseUrl($this->baseUrl())
            ->withToken(config('payments.fedapay.secret_key'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('payments.fedapay.timeout', 20));
    }

    private function unwrap(array $data): array
    {
        $entity = $data['v1/transaction'] ?? $data['transaction'] ?? $data;

        if (empty($entity['id'])) {
            throw new RuntimeException('Réponse FedaPay inattendue : transaction introuvable.');
        }

        return $entity;
    }

    private function errorMessage(RequestException $e): string
    {
        $json = $e->response->json();

        return (string) ($json['message'] ?? $json['error'] ?? 'erreur HTTP '.$e->response->status());
    }
}
