<?php

/*
| Paiements OCD Live.
|
| V1 : seul le pilote "manual" est actif. Le paiement est réglé hors ligne
| (Mobile Money, espèces, virement) puis enregistré par l'administration.
| Aucun paiement n'est jamais marqué comme réussi automatiquement.
|
| Pour brancher un agrégateur (CinetPay, FedaPay, PayDunya, Stripe…) :
|  1. créer une classe implémentant App\Payments\PaymentGateway ;
|  2. la déclarer ci-dessous dans "drivers" avec ses identifiants .env ;
|  3. exposer une route de webhook qui vérifie la signature du prestataire
|     avant d'appeler BookingWorkflow::recordPayment().
*/

return [
    'default' => env('PAYMENT_DRIVER', 'manual'),

    'drivers' => [
        'manual' => [
            'class' => App\Payments\ManualPaymentGateway::class,
        ],

        'mobile_money' => [
            'class' => null, // à implémenter : App\Payments\CinetPayGateway par exemple
            'api_key' => env('MOBILE_MONEY_API_KEY'),
            'site_id' => env('MOBILE_MONEY_SITE_ID'),
            'secret' => env('MOBILE_MONEY_SECRET'),
        ],

        'card' => [
            'class' => null, // à implémenter : App\Payments\StripeGateway par exemple
            'public_key' => env('CARD_PUBLIC_KEY'),
            'secret_key' => env('CARD_SECRET_KEY'),
            'webhook_secret' => env('CARD_WEBHOOK_SECRET'),
        ],
    ],
];
