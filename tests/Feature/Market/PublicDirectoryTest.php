<?php

namespace Tests\Feature\Market;

use App\Enums\LiveSubmissionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MarketFixtures;
use Tests\TestCase;

class PublicDirectoryTest extends TestCase
{
    use MarketFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedMarketReferenceData();
    }

    public function test_only_approved_and_non_expired_lives_are_visible(): void
    {
        $this->createApprovedSubmission(['seller_business_name' => 'Boutique Approuvée']);
        $this->createSubmission(['status' => LiveSubmissionStatus::Pending, 'seller_business_name' => 'Boutique En Attente']);
        $this->createSubmission(['status' => LiveSubmissionStatus::UnderReview, 'seller_business_name' => 'Boutique En Examen']);
        $this->createSubmission(['status' => LiveSubmissionStatus::Rejected, 'seller_business_name' => 'Boutique Refusée']);
        $this->createSubmission([
            'status' => LiveSubmissionStatus::Approved, 'seller_business_name' => 'Boutique Expirée',
            'approved_at' => now()->subHours(3), 'published_at' => now()->subHours(3), 'expires_at' => now()->subHour(),
        ]);

        $response = $this->get(route('market.index'));

        $response->assertOk()->assertSee('Boutique Approuvée')->assertDontSee('Boutique En Attente')
            ->assertDontSee('Boutique En Examen')->assertDontSee('Boutique Refusée')->assertDontSee('Boutique Expirée');
    }

    public function test_empty_state_is_shown_when_no_live_matches(): void
    {
        $this->get(route('market.index'))->assertOk()->assertSee('Aucun LIVE disponible pour ces critères.');
    }

    public function test_filters_can_be_combined(): void
    {
        $match = $this->createApprovedSubmission(['seller_business_name' => 'Boutique Correspondante']);
        $otherCategory = \App\Models\Category::where('slug', 'alimentation-et-gastronomie')->firstOrFail();
        $this->createApprovedSubmission(['seller_business_name' => 'Boutique Autre Catégorie', 'category_id' => $otherCategory->id]);

        $response = $this->get(route('market.index', [
            'country' => $match->country_id,
            'category' => $match->category_id,
        ]));

        $response->assertOk()->assertSee('Boutique Correspondante')->assertDontSee('Boutique Autre Catégorie');
    }

    public function test_country_page_shows_only_that_country_lives(): void
    {
        $togoLive = $this->createApprovedSubmission(['seller_business_name' => 'Boutique Togolaise']);
        $benin = \App\Models\Country::where('iso_code', 'BJ')->firstOrFail();
        $beninCity = $benin->cities()->first();
        $this->createApprovedSubmission(['seller_business_name' => 'Boutique Béninoise', 'country_id' => $benin->id, 'city_id' => $beninCity->id]);

        $response = $this->get(route('market.country', $togoLive->country));

        $response->assertOk()->assertSee('Boutique Togolaise')->assertDontSee('Boutique Béninoise');
    }

    public function test_results_are_paginated(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->createApprovedSubmission(['seller_business_name' => 'Boutique Numéro '.$i]);
        }

        $response = $this->get(route('market.index'));
        $response->assertOk()->assertSee('Boutique Numéro 0');

        $page2 = $this->get(route('market.index', ['page' => 2]));
        $page2->assertOk();
    }

    public function test_live_url_opens_in_a_new_tab(): void
    {
        $this->createApprovedSubmission();

        $this->get(route('market.index'))->assertOk()->assertSee('target="_blank"', false);
    }
}
