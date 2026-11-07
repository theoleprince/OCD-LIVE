<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Assistant = 'assistant';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Assistant => 'Assistante commerciale',
        };
    }
}
