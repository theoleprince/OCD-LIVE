<?php

namespace Tests\Feature\Market;

use App\Enums\LiveSubmissionStatus;
use App\Enums\UserRole;
use App\Models\AdminActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MarketFixtures;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use MarketFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedMarketReferenceData();
    }

    public function test_moderator_can_approve_a_pending_submission_and_it_becomes_public(): void
    {
        $submission = $this->createSubmission();
        $moderator = $this->marketUser(UserRole::Moderator);

        $this->actingAs($moderator)
            ->post(route('admin.market.submissions.approve', $submission))
            ->assertRedirect();

        $submission->refresh();
        $this->assertSame(LiveSubmissionStatus::Approved, $submission->status);
        $this->assertSame($moderator->id, $submission->moderator_id);
        $this->assertNotNull($submission->published_at);
        $this->assertNotNull($submission->expires_at);
        $this->assertTrue($submission->expires_at->greaterThan(now()));

        $this->get(route('market.index'))->assertSee($submission->account_name);
        $this->assertDatabaseHas('live_validations', ['submission_id' => $submission->id, 'action' => 'APPROVE']);
        $this->assertTrue(AdminActivityLog::where('action', 'market.approved')->where('subject_id', $submission->id)->exists());
    }

    public function test_moderator_can_reject_with_a_reason(): void
    {
        $submission = $this->createSubmission();
        $moderator = $this->marketUser(UserRole::Moderator);

        $this->actingAs($moderator)
            ->post(route('admin.market.submissions.reject', $submission), ['reason' => 'Compte TikTok introuvable.'])
            ->assertRedirect();

        $submission->refresh();
        $this->assertSame(LiveSubmissionStatus::Rejected, $submission->status);
        $this->assertSame('Compte TikTok introuvable.', $submission->rejection_reason);
        $this->get(route('market.index'))->assertDontSee($submission->account_name);
    }

    public function test_reject_requires_a_reason(): void
    {
        $submission = $this->createSubmission();
        $moderator = $this->marketUser(UserRole::Moderator);

        $this->actingAs($moderator)
            ->post(route('admin.market.submissions.reject', $submission), [])
            ->assertSessionHasErrors('reason');
    }

    public function test_moderator_can_hold_a_submission(): void
    {
        $submission = $this->createSubmission();
        $moderator = $this->marketUser(UserRole::Moderator);

        $this->actingAs($moderator)
            ->post(route('admin.market.submissions.hold', $submission), ['comment' => 'En attente de vérification complémentaire.'])
            ->assertRedirect();

        $this->assertSame(LiveSubmissionStatus::UnderReview, $submission->fresh()->status);
    }

    public function test_closed_submission_cannot_be_approved_again(): void
    {
        $submission = $this->createSubmission(['status' => LiveSubmissionStatus::Rejected]);
        $admin = $this->marketUser(UserRole::Admin);

        $this->actingAs($admin)
            ->post(route('admin.market.submissions.approve', $submission))
            ->assertSessionHasErrors('status');
    }

    public function test_admin_can_remove_a_published_live(): void
    {
        $submission = $this->createApprovedSubmission();
        $admin = $this->marketUser(UserRole::Admin);

        $this->actingAs($admin)->post(route('admin.market.lives.remove', $submission))->assertRedirect();

        $submission->refresh();
        $this->assertSame(LiveSubmissionStatus::Expired, $submission->status);
        $this->get(route('market.index'))->assertDontSee($submission->account_name);
    }

    public function test_assistant_has_no_access_to_market_admin(): void
    {
        $assistant = $this->marketUser(UserRole::Assistant);
        $submission = $this->createSubmission();

        $this->actingAs($assistant)->get(route('admin.market.dashboard'))->assertForbidden();
        $this->actingAs($assistant)->post(route('admin.market.submissions.approve', $submission))->assertForbidden();
    }

    public function test_moderator_cannot_manage_countries_or_moderators(): void
    {
        $moderator = $this->marketUser(UserRole::Moderator);

        $this->actingAs($moderator)->get(route('admin.market.countries.index'))->assertForbidden();
        $this->actingAs($moderator)->get(route('admin.market.moderators.index'))->assertForbidden();
        // Mais il accède bien au cœur de la modération.
        $this->actingAs($moderator)->get(route('admin.market.dashboard'))->assertOk();
        $this->actingAs($moderator)->get(route('admin.market.submissions.index'))->assertOk();
    }

    public function test_moderator_cannot_access_booking_admin_pages(): void
    {
        $moderator = $this->marketUser(UserRole::Moderator);

        $this->actingAs($moderator)->get('/admin')->assertForbidden();
        $this->actingAs($moderator)->get('/admin/reservations')->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $submission = $this->createSubmission();

        $this->get(route('admin.market.dashboard'))->assertRedirect(route('login'));
        $this->post(route('admin.market.submissions.approve', $submission))->assertRedirect(route('login'));
    }

    public function test_internal_comment_is_saved_without_changing_status(): void
    {
        $submission = $this->createSubmission();
        $admin = $this->marketUser(UserRole::Admin);

        $this->actingAs($admin)
            ->post(route('admin.market.submissions.comment', $submission), ['comment' => 'À surveiller.'])
            ->assertRedirect();

        $submission->refresh();
        $this->assertSame('À surveiller.', $submission->internal_comment);
        $this->assertSame(LiveSubmissionStatus::Pending, $submission->status);
    }

    public function test_admin_can_manage_countries_categories_and_schedules(): void
    {
        $admin = $this->marketUser(UserRole::Admin);

        $this->actingAs($admin)->post(route('admin.market.countries.store'), [
            'name' => 'Pays Test', 'iso_code' => 'ZZ', 'timezone' => 'UTC',
        ])->assertRedirect();
        $this->assertDatabaseHas('countries', ['iso_code' => 'ZZ', 'name' => 'Pays Test']);

        $this->actingAs($admin)->post(route('admin.market.categories.store'), ['name' => 'Catégorie Test'])->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Catégorie Test']);

        $this->actingAs($admin)->post(route('admin.market.schedules.store'), [
            'name' => 'Nuit', 'start_time' => '22:00', 'end_time' => '23:30',
        ])->assertRedirect();
        $this->assertDatabaseHas('live_schedules', ['name' => 'Nuit']);
    }

    public function test_admin_can_create_a_moderator_with_a_generated_password(): void
    {
        $admin = $this->marketUser(UserRole::Admin);

        $response = $this->actingAs($admin)->post(route('admin.market.moderators.store'), [
            'name' => 'Nouvelle Modératrice', 'email' => 'moderatrice@ocdlive.local',
        ]);

        $response->assertRedirect();
        $this->assertNotNull(session('generated_password'));
        $this->assertDatabaseHas('users', ['email' => 'moderatrice@ocdlive.local', 'role' => UserRole::Moderator->value]);
    }
}
