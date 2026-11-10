<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Assistant = 'assistant';
    case Moderator = 'moderator';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Assistant => 'Assistante commerciale',
            self::Moderator => 'Modérateur',
        };
    }

    /** Permissions OCD LIVE MARKET accordées à ce rôle. */
    public function marketPermissions(): array
    {
        return match ($this) {
            self::Admin => MarketPermission::cases(),
            self::Moderator => array_values(array_filter(
                MarketPermission::cases(),
                fn (MarketPermission $p) => $p !== MarketPermission::Manage,
            )),
            self::Assistant => [],
        };
    }
}
