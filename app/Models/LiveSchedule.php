<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveSchedule extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'timezone_policy', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LiveSubmission::class, 'schedule_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('start_time');
    }

    /** Ex. « Matin : 08h00 – 11h00 ». */
    public function getLabelAttribute(): string
    {
        return sprintf('%s : %s – %s', $this->name, substr($this->start_time, 0, 5), substr($this->end_time, 0, 5));
    }
}
