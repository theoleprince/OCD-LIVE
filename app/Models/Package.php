<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug', 'name', 'tagline', 'description', 'price', 'price_validated',
        'duration_minutes', 'duration_label', 'features', 'terms', 'max_products',
        'is_active', 'is_featured', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'integer',
            'duration_minutes' => 'integer',
            'price_validated' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function getFormattedPriceAttribute(): string
    {
        return money_fcfa($this->price);
    }

    /** Libellé lisible de la durée de la session en direct. */
    public function getDurationTextAttribute(): string
    {
        return $this->duration_label ?: duration_text($this->duration_minutes);
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
