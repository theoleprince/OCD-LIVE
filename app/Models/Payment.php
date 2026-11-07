<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'provider', 'method', 'amount', 'currency', 'status',
        'provider_reference', 'meta', 'paid_at', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'meta' => 'array',
            'paid_at' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class)->withTrashed();
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function methodLabels(): array
    {
        return [
            'mobile_money' => 'Mobile Money',
            'card' => 'Carte bancaire',
            'transfer' => 'Virement',
            'cash' => 'Espèces au studio',
        ];
    }
}
