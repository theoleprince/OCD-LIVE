<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case Failed = 'FAILED';
    case Refunded = 'REFUNDED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::Paid => 'Payé',
            self::Failed => 'Échoué',
            self::Refunded => 'Remboursé',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-800 ring-amber-200',
            self::Paid => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            self::Failed => 'bg-red-50 text-red-700 ring-red-200',
            self::Refunded => 'bg-neutral-100 text-neutral-600 ring-neutral-200',
        };
    }
}
