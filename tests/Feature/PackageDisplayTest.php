<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class PackageDisplayTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    public function test_the_six_packages_are_seeded_with_expected_prices(): void
    {
        $this->seedPackages();

        $this->assertSame(6, Package::count());
        $this->assertSame(15000, $this->starter()->price);
        $this->assertSame(90, $this->starter()->duration_minutes);
        $this->assertTrue($this->starter()->price_validated);
        $this->assertSame(5, Package::where('price_validated', false)->count());
    }

    public function test_home_page_lists_active_packages(): void
    {
        $this->seedPackages();

        $response = $this->get('/')->assertOk()
            ->assertSee('Votre produit mérite')
            ->assertSee('Starter Live')
            ->assertSee('15 000')
            ->assertSee('Partner of the Month')
            ->assertSee('RÉSERVER EN PRÉSENTIEL', false);

        $this->assertSame(6, substr_count($response->getContent(), 'data-testid="package-card"'));
    }

    public function test_inactive_packages_are_hidden(): void
    {
        $this->seedPackages();
        Package::where('slug', 'influence-boost')->update(['is_active' => false]);

        $this->get('/')->assertOk()->assertDontSee('Influence Boost');
    }
}
