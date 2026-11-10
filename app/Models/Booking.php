<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\ParticipationMode;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'payment_token', 'customer_id', 'business_id', 'package_id', 'mode', 'booking_date',
        'starts_at', 'ends_at', 'duration_minutes', 'amount', 'currency', 'status',
        'participant_timezone', 'connection_type', 'needs_assistance', 'assistance_notes',
        'products_count', 'customer_notes', 'admin_notes', 'stream_link', 'payment_due_at',
        'confirmed_at', 'cancelled_at', 'completed_at', 'cancellation_reason', 'is_demo', 'ip_address',
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            $booking->payment_token ??= Str::random(40);
        });
    }

    protected function casts(): array
    {
        return [
            'mode' => ParticipationMode::class,
            'status' => BookingStatus::class,
            'booking_date' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'payment_due_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'completed_at' => 'datetime',
            'needs_assistance' => 'boolean',
            'is_demo' => 'boolean',
            'amount' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class)->withTrashed();
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class)->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(BookingAttachment::class);
    }

    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', BookingStatus::blocking());
    }

    public function scopeOverlapping(Builder $query, \DateTimeInterface $start, \DateTimeInterface $end): Builder
    {
        return $query->where('starts_at', '<', $end)->where('ends_at', '>', $start);
    }

    public function isPaid(): bool
    {
        return $this->payments()->where('status', PaymentStatus::Paid->value)->exists();
    }

    public function paymentStatus(): PaymentStatus
    {
        $payment = $this->relationLoaded('latestPayment') ? $this->latestPayment : $this->latestPayment()->first();

        return $payment?->status ?? PaymentStatus::Pending;
    }

    public function getTimeRangeAttribute(): string
    {
        return $this->starts_at->format('H\hi').' – '.$this->ends_at->format('H\hi');
    }

    /** Horaire exprimé dans le fuseau du participant (clients à distance). */
    public function getParticipantTimeRangeAttribute(): ?string
    {
        if (! $this->participant_timezone || $this->participant_timezone === config('ocd.timezone')) {
            return null;
        }

        $tz = $this->participant_timezone;

        return $this->starts_at->copy()->setTimezone($tz)->format('H\hi').' – '
            .$this->ends_at->copy()->setTimezone($tz)->format('H\hi').' ('.$tz.')';
    }
}
