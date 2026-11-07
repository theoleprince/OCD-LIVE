<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPackages();
    }

    public function test_pages_are_mobile_ready(): void
    {
        $iphone = ['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148'];

        foreach (['/', '/reserver', '/conditions-generales', '/confidentialite', '/connexion'] as $uri) {
            $this->withHeaders($iphone)->get($uri)->assertOk()
                ->assertSee('name="viewport" content="width=device-width, initial-scale=1', false);
        }

        // Menu mobile dédié + boutons d'action présents dans le HTML responsive.
        $this->withHeaders($iphone)->get('/')
            ->assertSee('data-testid="mobile-menu-button"', false)
            ->assertSee('id="menu-mobile"', false)
            ->assertSee('lg:hidden', false)
            ->assertSee('RÉSERVER À DISTANCE', false);
    }

    public function test_seo_metadata_is_present(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('<title>OCD LIVE — Votre produit mérite d&#039;être vu</title>', false)
            ->assertSee('name="description"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('rel="canonical"', false);

        $this->get('/reserver')->assertSee('<title>Réserver mon Live | OCD LIVE</title>', false);
        $this->get('/connexion')->assertSee('noindex', false);
    }

    public function test_sitemap_and_robots(): void
    {
        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('booking'), false);

        $this->get('/robots.txt')->assertOk()->assertSee('User-agent: *');
    }

    public function test_health_endpoint(): void
    {
        $this->get('/up')->assertOk();
    }
}
