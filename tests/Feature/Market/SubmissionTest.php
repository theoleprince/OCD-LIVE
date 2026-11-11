<?php

namespace Tests\Feature\Market;

use App\Enums\LiveSubmissionStatus;
use App\Livewire\LiveSubmissionForm;
use App\Models\LiveSubmission;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\MarketFixtures;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use MarketFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedMarketReferenceData();
        Notification::fake();
        Storage::fake('public');
        RateLimiter::clear('market-submit:127.0.0.1');
    }

    private function fillForm(array $overrides = [])
    {
        $data = $this->submissionPayload($overrides);
        $component = Livewire::test(LiveSubmissionForm::class);

        foreach ($data as $key => $value) {
            $component->set($key, $value);
        }

        return $component;
    }

    public function test_valid_submission_creates_a_pending_request_with_a_unique_reference_and_token(): void
    {
        $this->fillForm()->call('submit')->assertHasNoErrors()->assertSet('submitted', true);

        $submission = LiveSubmission::with('seller')->sole();
        $this->assertMatchesRegularExpression('/^MKT-\d{4}-\d{4}$/', $submission->reference);
        $this->assertSame(40, strlen($submission->tracking_token));
        $this->assertSame(LiveSubmissionStatus::Pending, $submission->status);
        $this->assertSame('Boutique Ama', $submission->seller->business_name);
        $this->assertNotNull($submission->submitted_at);
    }

    public function test_invalid_tiktok_url_is_rejected(): void
    {
        $this->fillForm(['liveUrl' => 'https://www.instagram.com/ama'])
            ->call('submit')
            ->assertHasErrors(['liveUrl']);

        $this->assertSame(0, LiveSubmission::count());
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::test(LiveSubmissionForm::class)
            ->call('submit')
            ->assertHasErrors(['businessName', 'phone', 'countryId', 'cityId', 'categoryId', 'productDescription', 'accountName', 'liveUrl', 'acceptTerms', 'authorizePublication', 'confirmOwnership']);
    }

    public function test_short_form_collects_only_essential_fields_and_derives_the_account(): void
    {
        Livewire::test(LiveSubmissionForm::class)
            ->call('next')->assertHasErrors(['liveUrl', 'productDescription'])->assertSet('step', 1)
            ->set('liveUrl', 'https://www.tiktok.com/@ama_boutique/live')
            ->set('productDescription', 'Des pagnes et des robes confectionnées dans notre atelier.')
            ->call('next')->assertHasNoErrors()->assertSet('step', 2)
            ->set('businessName', 'Boutique Ama')->set('phone', '+22890000001')
            ->call('next')->assertHasNoErrors()->assertSet('step', 3)
            ->set('countryId', $this->togo()->id)->set('cityId', $this->lome()->id)
            ->call('next')->assertHasNoErrors()->assertSet('step', 4)
            ->call('back')->assertSet('step', 3)->assertSet('businessName', 'Boutique Ama')
            ->call('next')->set('categoryId', $this->category()->id)->set('acceptAll', true)
            ->call('submit')->assertHasNoErrors()->assertSet('submitted', true);

        $submission = LiveSubmission::with('seller')->sole();
        $this->assertSame('@ama_boutique', $submission->account_name);
        $this->assertSame('Boutique Ama', $submission->seller->full_name);
        $this->assertNull($submission->schedule_id);
        $this->assertTrue($submission->consents['publication']);
    }

    public function test_unchecking_combined_consent_prevents_submission(): void
    {
        $this->fillForm()->set('acceptAll', true)->set('acceptAll', false)
            ->call('submit')->assertHasErrors(['acceptTerms', 'authorizePublication', 'confirmOwnership']);
        $this->assertSame(0, LiveSubmission::count());
    }

    public function test_omitting_an_optional_name_preserves_an_existing_seller_name(): void
    {
        $this->fillForm()->call('submit')->assertHasNoErrors();
        LiveSubmission::sole()->update(['status' => LiveSubmissionStatus::Expired]);

        $this->fillForm(['fullName' => '', 'scheduleId' => null, 'accountName' => ''])
            ->call('submit')->assertHasNoErrors();

        $this->assertSame(1, Seller::count());
        $this->assertSame('Ama Koffi', Seller::sole()->full_name);
    }

    public function test_duplicate_active_submission_for_the_same_tiktok_account_is_rejected(): void
    {
        $this->fillForm()->call('submit')->assertHasNoErrors();

        $this->fillForm(['productDescription' => 'Une autre description suffisamment longue.'])
            ->call('submit')
            ->assertHasErrors(['submit']);

        // Un seul vendeur, une seule demande : pas de doublon créé.
        $this->assertSame(1, Seller::count());
        $this->assertSame(1, LiveSubmission::count());
    }

    public function test_honeypot_blocks_submission(): void
    {
        try {
            $this->fillForm()->set('website', 'http://spam.example')->call('submit');
        } catch (\Throwable $e) {
            // abort(422) interrompt la requête : peu importe comment Livewire restitue l'exception en test,
            // ce qui compte est qu'aucune demande n'ait été créée.
        }

        $this->assertSame(0, LiveSubmission::count());
    }

    public function test_submission_is_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->fillForm(['accountName' => '@vendeur'.$i, 'liveUrl' => 'https://www.tiktok.com/@vendeur'.$i.'/live'])
                ->call('submit')
                ->assertHasNoErrors();
        }

        $this->fillForm(['accountName' => '@vendeur_extra', 'liveUrl' => 'https://www.tiktok.com/@vendeur_extra/live'])
            ->call('submit')
            ->assertHasErrors(['submit']);

        $this->assertSame(5, LiveSubmission::count());
    }

    public function test_profile_image_is_stored_on_the_public_disk(): void
    {
        $this->fillForm()->set('profileImage', UploadedFile::fake()->image('photo.jpg', 300, 300))
            ->call('submit')->assertHasNoErrors();

        $seller = Seller::sole();
        $this->assertNotNull($seller->profile_image);
        $this->assertStringStartsWith('market/sellers/'.$seller->id.'/', $seller->profile_image);
        Storage::disk('public')->assertExists($seller->profile_image);
    }
}
