<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Réglage d'une journée précise du calendrier. Les champs à null
 * reprennent les réglages hebdomadaires (table settings).
 */
class AvailabilitySlot extends Model
{
    protected $fillable = ['date', 'is_open', 'opens_at', 'closes_at', 'note'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_open' => 'boolean',
        ];
    }
}
