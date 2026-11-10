<?php

namespace App\Console\Commands;

use App\Enums\LiveSubmissionStatus;
use App\Models\AdminActivityLog;
use App\Models\LiveSubmission;
use Illuminate\Console\Command;

class ExpireLiveSubmissions extends Command
{
    protected $signature = 'market:expire-lives {--dry-run : Affiche sans modifier}';

    protected $description = 'Passe au statut EXPIRED les publications OCD LIVE MARKET dont la durée de visibilité est dépassée.';

    public function handle(): int
    {
        $expired = LiveSubmission::query()
            ->where('status', LiveSubmissionStatus::Approved->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $submission) {
            $this->line(($this->option('dry-run') ? '[simulation] ' : '').$submission->reference.' — expirée depuis '.$submission->expires_at->format('d/m/Y H:i'));

            if (! $this->option('dry-run')) {
                $submission->update(['status' => LiveSubmissionStatus::Expired]);
                AdminActivityLog::record('market.expired', $submission);
            }
        }

        $this->info($expired->count().' publication(s) '.($this->option('dry-run') ? 'concernée(s).' : 'expirée(s).'));

        return self::SUCCESS;
    }
}
