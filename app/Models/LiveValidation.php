<?php

namespace App\Models;

use App\Enums\ModerationAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveValidation extends Model
{
    protected $fillable = ['submission_id', 'moderator_id', 'action', 'comment'];

    protected function casts(): array
    {
        return ['action' => ModerationAction::class];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(LiveSubmission::class, 'submission_id');
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
