<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seller extends Model
{
    protected $fillable = [
        'full_name', 'business_name', 'phone', 'country_id', 'city_id', 'description',
        'profile_image', 'logo_image', 'public_slug', 'consents',
    ];

    protected function casts(): array
    {
        return ['consents' => 'array'];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LiveSubmission::class);
    }

    /** Un live encore visible publiquement (approuvé, non expiré). */
    public function activeSubmissions(): HasMany
    {
        return $this->submissions()->published();
    }

    public function hasPublicProfile(): bool
    {
        return (bool) $this->public_slug && (bool) ($this->consents['publication'] ?? false);
    }

    public function getRouteKeyName(): string
    {
        return 'public_slug';
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        if (! $this->profile_image) {
            return null;
        }

        // Les illustrations de démonstration sont livrées avec le site, hors du volume d'uploads.
        return str_starts_with($this->profile_image, 'market/demo/')
            ? asset('images/'.$this->profile_image)
            : asset('storage/'.$this->profile_image);
    }
}
