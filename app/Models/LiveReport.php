<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveReport extends Model
{
    protected $fillable = ['submission_id', 'reporter_contact', 'reason', 'description', 'status', 'resolved_at', 'ip_address'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(LiveSubmission::class, 'submission_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'OPEN');
    }
}
