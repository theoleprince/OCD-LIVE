<?php

namespace App\Enums;

enum ModerationAction: string
{
    case Approve = 'APPROVE';
    case Reject = 'REJECT';
    case Hold = 'HOLD';
    case Remove = 'REMOVE';

    public function label(): string
    {
        return match ($this) {
            self::Approve => 'Approuvée',
            self::Reject => 'Refusée',
            self::Hold => 'Mise en attente',
            self::Remove => 'Retirée',
        };
    }
}
