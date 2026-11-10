<?php

namespace Tests\Concerns;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Enums\LiveSubmissionStatus;
use App\Models\LiveSchedule;
use App\Models\LiveSubmission;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\LiveScheduleSeeder;
use Illuminate\Support\Str;

trait MarketFixtures
{
    protected function seedMarketReferenceData(): void
    {
        $this->seed(CountrySeeder::class);
        $this->seed(CitySeeder::class);
        $this->seed(CategorySeeder::class);
        $this->seed(LiveScheduleSeeder::class);
    }

    protected function marketUser(UserRole $role = UserRole::Admin): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    protected function togo(): Country
    {
        return Country::where('iso_code', 'TG')->firstOrFail();
    }

    protected function lome(): City
    {
        return $this->togo()->cities()->where('name', 'Lomé')->firstOrFail();
    }

    protected function category(): Category
    {
        return Category::where('slug', 'mode-et-vetements')->firstOrFail();
    }

    protected function schedule(): LiveSchedule
    {
        return LiveSchedule::where('name', 'Matin')->firstOrFail();
    }

    protected function submissionPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'fullName' => 'Ama Koffi',
            'businessName' => 'Boutique Ama',
            'phone' => '+22890000001',
            'countryId' => $this->togo()->id,
            'cityId' => $this->lome()->id,
            'description' => '',
            'categoryId' => $this->category()->id,
            'subcategoryId' => '',
            'productDescription' => 'Vente de pagnes et tissus africains de qualité supérieure.',
            'accountName' => '@ama_boutique',
            'liveUrl' => 'https://www.tiktok.com/@ama_boutique/live',
            'scheduleId' => $this->schedule()->id,
            'acceptTerms' => true,
            'authorizePublication' => true,
            'confirmOwnership' => true,
        ], $overrides);
    }

    /** Crée directement (hors formulaire) un vendeur + une demande, dans le statut souhaité. */
    protected function createSubmission(array $overrides = []): LiveSubmission
    {
        $seller = Seller::create([
            'full_name' => $overrides['seller_full_name'] ?? 'Ama Koffi',
            'business_name' => $overrides['seller_business_name'] ?? 'Boutique Ama',
            'phone' => '+2289000'.random_int(1000, 9999),
            'country_id' => $overrides['country_id'] ?? $this->togo()->id,
            'city_id' => $overrides['city_id'] ?? $this->lome()->id,
            'consents' => ['terms' => true, 'publication' => true, 'ownership' => true],
        ]);

        return LiveSubmission::create([
            'reference' => 'MKT-TEST-'.Str::random(8),
            'tracking_token' => Str::random(40),
            'seller_id' => $seller->id,
            'country_id' => $seller->country_id,
            'city_id' => $seller->city_id,
            'category_id' => $overrides['category_id'] ?? $this->category()->id,
            'schedule_id' => $overrides['schedule_id'] ?? $this->schedule()->id,
            'platform' => 'tiktok',
            'account_name' => $overrides['account_name'] ?? '@ama_boutique',
            'live_url' => 'https://www.tiktok.com/@ama_boutique/live',
            'product_description' => 'Vente de pagnes et tissus africains.',
            'status' => $overrides['status'] ?? LiveSubmissionStatus::Pending,
            'submitted_at' => now(),
            'approved_at' => $overrides['approved_at'] ?? null,
            'published_at' => $overrides['published_at'] ?? null,
            'expires_at' => $overrides['expires_at'] ?? null,
        ]);
    }

    protected function createApprovedSubmission(array $overrides = []): LiveSubmission
    {
        return $this->createSubmission([
            'status' => LiveSubmissionStatus::Approved,
            'approved_at' => now(),
            'published_at' => now(),
            'expires_at' => now()->addMinutes(90),
            ...$overrides,
        ]);
    }
}
