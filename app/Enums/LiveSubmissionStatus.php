<?php

namespace App\Enums;

enum LiveSubmissionStatus: string
{
    case Pending = 'PENDING';
    case UnderReview = 'UNDER_REVIEW';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
    case Expired = 'EXPIRED';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::UnderReview => 'En cours d\'examen',
            self::Approved => 'Approuvée',
            self::Rejected => 'Refusée',
            self::Expired => 'Expirée',
            self::Cancelled => 'Annulée',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-800 ring-amber-200',
            self::UnderReview => 'bg-sky-50 text-sky-800 ring-sky-200',
            self::Approved => 'bg-emerald-50 text-emerald-800 ring-emerald-200',
            self::Rejected => 'bg-red-50 text-red-700 ring-red-200',
            self::Expired => 'bg-neutral-100 text-neutral-500 ring-neutral-200',
            self::Cancelled => 'bg-neutral-100 text-neutral-500 ring-neutral-200',
        };
    }

    /** Statuts qui ne peuvent plus faire l'objet d'une action de modération. */
    public static function closed(): array
    {
        return [self::Rejected->value, self::Expired->value, self::Cancelled->value];
    }
}
