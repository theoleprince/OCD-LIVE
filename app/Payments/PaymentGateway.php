<?php

namespace App\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /** Identifiant du pilote (manual, mobile_money, card…). */
    public function name(): string;

    /**
     * Démarre un paiement. Retourne soit une URL de redirection vers le prestataire,
     * soit des instructions à afficher au client. Ne marque jamais le paiement comme payé.
     */
    public function initiate(Payment $payment): PaymentInitiation;
}
