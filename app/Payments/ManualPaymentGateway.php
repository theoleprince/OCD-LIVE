<?php

namespace App\Payments;

use App\Models\Payment;

class ManualPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'manual';
    }

    public function initiate(Payment $payment): PaymentInitiation
    {
        return new PaymentInitiation(instructions: [
            'Votre créneau est réservé provisoirement pendant '.setting('payment_hold_hours').' heures.',
            'Notre équipe vous contacte sur WhatsApp pour le règlement de '.money_fcfa($payment->amount).' (Mobile Money ou espèces au studio).',
            'Indiquez la référence '.$payment->booking->reference.' lors de votre paiement.',
            'La réservation est confirmée uniquement après réception du paiement et validation par notre équipe.',
        ]);
    }
}
