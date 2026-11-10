<?php

namespace Tests\Feature\Market;

use App\Enums\LiveSubmissionStatus;
use App\Models\AdminActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MarketFixtures;
use Tests\TestCase;

class ExpirationTest extends TestCase
{
    use MarketFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedMarketReferenceData();
    }

    public function test_command_expires_approved_submissions_past_their_expiry(): void
    {
        $expired = $this->createApprovedSubmission([
            'account_name' => '@expired', 'approved_at' => now()->subHours(2),
            'published_at' => now()->subHours(2), 'expires_at' => now()->subHour(),
        ]);
        $stillLive = $this->createApprovedSubmission(['account_name' => '@stilllive']);

        $this->artisan('market:expire-lives')->assertSuccessful();

        $this->assertSame(LiveSubmissionStatus::Expired, $expired->fresh()->status);
        $this->assertSame(LiveSubmissionStatus::Approved, $stillLive->fresh()->status);
        $this->assertTrue(AdminActivityLog::where('action', 'market.expired')->where('subject_id', $expired->id)->exists());
    }

    public function test_dry_run_does_not_modify_anything(): void
    {
        $expired = $this->createApprovedSubmission(['expires_at' => now()->subHour()]);

        $this->artisan('market:expire-lives', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(LiveSubmissionStatus::Approved, $expired->fresh()->status);
    }

    public function test_public_listing_hides_expired_lives_even_if_the_command_has_not_run_yet(): void
    {
        // Garde-fou applicatif : même sans tâche planifiée récente, l'annuaire public ne montre jamais un live expiré.
        $submission = $this->createApprovedSubmission(['account_name' => '@stale', 'expires_at' => now()->subMinute()]);

        $this->get(route('market.index'))->assertDontSee('@stale');
        $this->assertSame(LiveSubmissionStatus::Approved, $submission->fresh()->status);
    }
}
