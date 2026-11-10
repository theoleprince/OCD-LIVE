<?php

namespace Tests\Feature\Market;

use App\Enums\LiveSubmissionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MarketFixtures;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use MarketFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedMarketReferenceData();
    }

    public function test_valid_token_shows_pending_message(): void
    {
        $submission = $this->createSubmission();

        $this->get(route('market.track', $submission->tracking_token))
            ->assertOk()
            ->assertSee($submission->reference)
            ->assertSee('Votre demande a bien été reçue par OCD Live', false);
    }

    public function test_approved_submission_shows_congratulations_and_live_link(): void
    {
        $submission = $this->createApprovedSubmission();

        $this->get(route('market.track', $submission->tracking_token))
            ->assertOk()
            ->assertSee('Félicitations', false)
            ->assertSee($submission->live_url, false);
    }

    public function test_rejected_submission_shows_reason(): void
    {
        $submission = $this->createSubmission(['status' => LiveSubmissionStatus::Rejected]);
        $submission->update(['rejection_reason' => 'Lien TikTok invalide.']);

        $this->get(route('market.track', $submission->tracking_token))
            ->assertOk()
            ->assertSee('Lien TikTok invalide.');
    }

    public function test_internal_comment_is_never_exposed(): void
    {
        $submission = $this->createSubmission();
        $submission->update(['internal_comment' => 'Vérifier la fiabilité du vendeur avant publication.']);

        $this->get(route('market.track', $submission->tracking_token))
            ->assertOk()
            ->assertDontSee('Vérifier la fiabilité du vendeur avant publication.');
    }

    public function test_unknown_token_returns_404(): void
    {
        $this->get(route('market.track', 'un-jeton-qui-n-existe-pas'))->assertNotFound();
    }

    public function test_tracking_page_has_no_mutation_endpoint(): void
    {
        $submission = $this->createSubmission();

        // Aucune route POST n'existe pour /market/suivi/{token} : impossible de modifier le statut depuis cette page.
        $this->post('/market/suivi/'.$submission->tracking_token)->assertStatus(405);
        $this->assertSame(LiveSubmissionStatus::Pending, $submission->fresh()->status);
    }

    public function test_tracking_page_is_not_indexable(): void
    {
        $submission = $this->createSubmission();

        $this->get(route('market.track', $submission->tracking_token))
            ->assertSee('noindex', false);
    }
}
