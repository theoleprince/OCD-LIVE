<?php

namespace App\Enums;

enum ParticipationMode: string
{
    case Onsite = 'ONSITE';
    case Remote = 'REMOTE';

    public function label(): string
    {
        return match ($this) {
            self::Onsite => 'Présentiel – Lomé',
            self::Remote => 'À distance',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Onsite => 'Présentiel',
            self::Remote => 'Distance',
        };
    }
}
