<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PendingPayment = 'PENDING_PAYMENT';
    case PendingReview = 'PENDING_REVIEW';
    case Confirmed = 'CONFIRMED';
    case Cancelled = 'CANCELLED';
    case Completed = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'En attente de paiement',
            self::PendingReview => 'En attente de validation',
            self::Confirmed => 'Confirmée',
            self::Cancelled => 'Annulée',
            self::Completed => 'Terminée',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PendingPayment => 'bg-amber-50 text-amber-800 ring-amber-200',
            self::PendingReview => 'bg-sky-50 text-sky-800 ring-sky-200',
            self::Confirmed => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            self::Cancelled => 'bg-neutral-100 text-neutral-500 ring-neutral-200',
            self::Completed => 'bg-slate-800 text-white ring-slate-800',
        };
    }

    /** Statuts qui occupent un créneau dans le calendrier. */
    public static function blocking(): array
    {
        return [self::PendingPayment->value, self::PendingReview->value, self::Confirmed->value, self::Completed->value];
    }
}
