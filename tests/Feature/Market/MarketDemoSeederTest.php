<?php

namespace Tests\Feature\Market;

use App\Models\LiveSubmission;
use App\Models\Seller;
use Database\Seeders\MarketDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_covers_two_months_and_can_be_refreshed_without_duplicates(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 11)->setTime(12, 0));
        Notification::fake();
        Storage::fake('public');

        $this->seed(MarketDemoSeeder::class);

        $this->assertSame(24, Seller::count());
        $this->assertSame(276, LiveSubmission::count());
        $this->assertSame(24, LiveSubmission::published()->count());
        $this->assertSame('2026-09-11', LiveSubmission::min('created_at') ? substr(LiveSubmission::min('created_at'), 0, 10) : null);
        $this->assertSame(6, Seller::distinct()->count('country_id'));
        $this->assertSame(4, LiveSubmission::distinct()->count('category_id'));

        $tokens = LiveSubmission::orderBy('reference')->pluck('tracking_token')->all();
        $this->seed(MarketDemoSeeder::class);
        $this->assertSame(24, Seller::count());
        $this->assertSame(276, LiveSubmission::count());
        $this->assertSame($tokens, LiveSubmission::orderBy('reference')->pluck('tracking_token')->all());
        $demo = LiveSubmission::published()->firstOrFail();
        $this->assertTrue($demo->isDemo());
        $this->get('/market')->assertOk()->assertSee('DÉMONSTRATION')
            ->assertSee('Suivre le live')->assertSee('href="https://www.tiktok.com/live"', false)
            ->assertSee('/images/market/demo/', false)
            ->assertDontSee('Découvrir le commerce');
        $this->get(route('market.seller', $demo->seller))->assertOk();

        $this->travel(91)->minutes();
        $this->assertSame(24, LiveSubmission::published()->count());
        $this->travelTo(now()->addMonthsNoOverflow(2)->addDay());
        $this->assertSame(0, LiveSubmission::published()->count());
        Notification::assertNothingSent();
    }

    public function test_demo_seed_does_not_run_in_production(): void
    {
        $this->app->instance('env', 'production');
        $this->app->make(MarketDemoSeeder::class)->run();
        $this->assertSame(0, Seller::count());
        $this->assertSame(0, LiveSubmission::count());
    }

    public function test_production_can_explicitly_enable_market_demonstration_data(): void
    {
        $this->app->instance('env', 'production');
        config(['ocd.market_demo_enabled' => true]);
        Storage::fake('public');
        $this->app->make(MarketDemoSeeder::class)->run();

        $this->assertSame(24, Seller::count());
        $this->assertSame(24, LiveSubmission::published()->count());
    }
}
