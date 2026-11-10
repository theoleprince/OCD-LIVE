<?php

namespace App\Services;

use App\Enums\LiveSubmissionStatus;
use App\Enums\ModerationAction;
use App\Models\AdminActivityLog;
use App\Models\LiveSubmission;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Transitions de statut d'une demande OCD LIVE MARKET, déclenchées par un modérateur ou un
 * administrateur. Aucune transition n'est automatique : la publication exige toujours une
 * vérification humaine du lien TikTok (cahier des charges §8).
 */
class ModerationWorkflow
{
    public function approve(LiveSubmission $submission, User $moderator, ?string $comment = null): void
    {
        $this->ensureOpen($submission);

        $submission->update([
            'status' => LiveSubmissionStatus::Approved,
            'moderator_id' => $moderator->id,
            'reviewed_at' => $submission->reviewed_at ?? now(),
            'approved_at' => now(),
            'published_at' => now(),
            'expires_at' => now()->addMinutes((int) setting('market_publication_minutes', 90)),
            'internal_comment' => $comment ?: $submission->internal_comment,
        ]);

        $this->log($submission, $moderator, ModerationAction::Approve, $comment, 'market.approved');
    }

    public function reject(LiveSubmission $submission, User $moderator, string $reason, ?string $comment = null): void
    {
        $this->ensureOpen($submission);

        $submission->update([
            'status' => LiveSubmissionStatus::Rejected,
            'moderator_id' => $moderator->id,
            'reviewed_at' => $submission->reviewed_at ?? now(),
            'rejection_reason' => $reason,
            'internal_comment' => $comment ?: $submission->internal_comment,
        ]);

        $this->log($submission, $moderator, ModerationAction::Reject, $reason, 'market.rejected');
    }

    public function hold(LiveSubmission $submission, User $moderator, ?string $comment = null): void
    {
        $this->ensureOpen($submission);

        $submission->update([
            'status' => LiveSubmissionStatus::UnderReview,
            'moderator_id' => $moderator->id,
            'reviewed_at' => $submission->reviewed_at ?? now(),
            'internal_comment' => $comment ?: $submission->internal_comment,
        ]);

        $this->log($submission, $moderator, ModerationAction::Hold, $comment, 'market.held');
    }

    public function cancel(LiveSubmission $submission, User $moderator, ?string $comment = null): void
    {
        $this->ensureOpen($submission);

        $submission->update([
            'status' => LiveSubmissionStatus::Cancelled,
            'moderator_id' => $moderator->id,
            'internal_comment' => $comment ?: $submission->internal_comment,
        ]);

        AdminActivityLog::record('market.cancelled', $submission, ['comment' => $comment]);
    }

    /** Retire immédiatement une publication active (§11.4). */
    public function remove(LiveSubmission $submission, User $moderator, ?string $comment = null): void
    {
        if ($submission->status !== LiveSubmissionStatus::Approved) {
            throw ValidationException::withMessages(['status' => 'Seule une publication approuvée peut être retirée.']);
        }

        $submission->update([
            'status' => LiveSubmissionStatus::Expired,
            'expires_at' => now(),
            'internal_comment' => $comment ?: $submission->internal_comment,
        ]);

        $this->log($submission, $moderator, ModerationAction::Remove, $comment, 'market.removed');
    }

    private function ensureOpen(LiveSubmission $submission): void
    {
        if (in_array($submission->status->value, LiveSubmissionStatus::closed(), true)) {
            throw ValidationException::withMessages(['status' => 'Cette demande est déjà clôturée (refusée, expirée ou annulée).']);
        }
    }

    private function log(LiveSubmission $submission, User $moderator, ModerationAction $action, ?string $comment, string $logAction): void
    {
        $submission->validations()->create([
            'moderator_id' => $moderator->id,
            'action' => $action,
            'comment' => $comment,
        ]);

        AdminActivityLog::record($logAction, $submission, ['comment' => $comment]);
    }
}
