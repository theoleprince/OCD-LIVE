<?php

/*
| Paiements OCD Live.
|
| Après validation de sa réservation, le client choisit son mode de paiement :
|  - en ligne via FedaPay : Moov Money (Flooz), Mixx by Yas (ex-T-Money), carte Visa/Mastercard ;
|  - hors ligne : virement bancaire ou espèces au studio (enregistré ensuite par l'administration).
|
| Les modes en ligne ne sont proposés que si FEDAPAY_SECRET_KEY est renseignée. Un paiement en
| ligne n'est jamais considéré comme réussi sur la seule foi du navigateur ou d'un webhook :
| son statut est toujours relu auprès de l'API FedaPay avant d'être enregistré.
*/

return [
    'default' => env('PAYMENT_DRIVER', 'manual'),

    'drivers' => [
        'manual' => [
            'class' => App\Payments\ManualPaymentGateway::class,
        ],
    ],

    'fedapay' => [
        // sandbox (tests, aucun débit réel) ou live
        'environment' => env('FEDAPAY_ENVIRONMENT', 'sandbox'),
        'secret_key' => env('FEDAPAY_SECRET_KEY'),
        // Clé secrète du webhook (tableau de bord FedaPay > Webhooks), URL : /webhooks/fedapay
        'webhook_secret' => env('FEDAPAY_WEBHOOK_SECRET'),
        'country' => 'TG',
        'dial_code' => '228',
        'timeout' => 20,
    ],

    /*
    | Modes proposés au client, dans l'ordre d'affichage.
    | fedapay_mode : code API FedaPay du paiement mobile sans redirection (null = page FedaPay).
    */
    'methods' => [
        'moov_money' => [
            'label' => 'Moov Money (Flooz)',
            'hint' => 'Moov Africa Togo',
            'logo' => 'images/paiement/moov-money.jpg',
            'type' => 'mobile',
            'fedapay_mode' => 'moov_tg',
        ],
        'mixx' => [
            'label' => 'Mixx by Yas',
            'hint' => 'Ex-T-Money · Yas Togo',
            'logo' => 'images/paiement/mixx-by-yas.svg',
            'type' => 'mobile',
            'fedapay_mode' => 'togocel',
        ],
        'card' => [
            'label' => 'Carte bancaire',
            'hint' => 'Visa · Mastercard',
            'logos' => ['images/paiement/visa.svg', 'images/paiement/mastercard.svg'],
            'type' => 'card',
            'fedapay_mode' => null,
        ],
        'transfer' => [
            'label' => 'Virement bancaire',
            'hint' => 'Validation sous 24 à 48 h',
            'icon' => 'bank',
            'type' => 'manual',
        ],
        'cash' => [
            'label' => 'Espèces au studio',
            'hint' => 'Studio OCD Live, Lomé',
            'icon' => 'cash',
            'type' => 'manual',
        ],
    ],
];
