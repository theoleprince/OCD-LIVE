<?php

namespace App\Models;

use App\Enums\LiveSubmissionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveSubmission extends Model
{
    protected $fillable = [
        'reference', 'tracking_token', 'seller_id', 'country_id', 'city_id', 'category_id',
        'subcategory_id', 'schedule_id', 'moderator_id', 'platform', 'account_name', 'live_url',
        'product_description', 'consents', 'status', 'submitted_at', 'reviewed_at', 'approved_at',
        'published_at', 'expires_at', 'rejection_reason', 'internal_comment', 'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'status' => LiveSubmissionStatus::class,
            'consents' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(LiveSchedule::class, 'schedule_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(LiveValidation::class, 'submission_id')->latest();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(LiveReport::class, 'submission_id');
    }

    /**
     * Visible dans l'annuaire public : approuvé ET non expiré.
     * Garde-fou appliqué à chaque requête publique, en plus de la tâche planifiée d'expiration.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', LiveSubmissionStatus::Approved->value)
            ->where('expires_at', '>', now());
    }

    public function isPublished(): bool
    {
        return $this->status === LiveSubmissionStatus::Approved && $this->expires_at?->isFuture();
    }
}
