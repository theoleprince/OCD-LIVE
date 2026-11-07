<?php

namespace Tests\Feature;

use App\Models\AdminActivityLog;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPackages();
    }

    public function test_admin_can_update_price_description_duration_and_features(): void
    {
        $package = Package::where('slug', 'business-live')->first();

        $this->actingAs($this->admin())->put(route('admin.packages.update', $package), [
            'name' => 'Business Live',
            'slug' => 'business-live',
            'price' => 50000,
            'price_validated' => 1,
            'duration_minutes' => 210,
            'description' => 'Nouvelle description',
            'features_text' => "Présentation approfondie\n- Plusieurs produits\n\n",
            'terms' => 'Modalités mises à jour',
            'sort_order' => 2,
            'is_active' => 1,
        ])->assertRedirect(route('admin.packages.index'));

        $package->refresh();
        $this->assertSame(50000, $package->price);
        $this->assertTrue($package->price_validated);
        $this->assertSame(210, $package->duration_minutes);
        $this->assertSame(['Présentation approfondie', 'Plusieurs produits'], $package->features);
        $this->assertSame('Modalités mises à jour', $package->terms);
        $this->assertTrue(AdminActivityLog::where('action', 'package.updated')->exists());

        $this->get('/')->assertSee('50 000');
    }

    public function test_admin_can_create_toggle_and_soft_delete_a_package(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.packages.store'), [
            'name' => 'Flash Live', 'price' => 10000, 'duration_minutes' => 60, 'sort_order' => 7, 'is_active' => 1,
        ])->assertRedirect();
        $flash = Package::where('slug', 'flash-live')->sole();
        $this->assertTrue($flash->is_active);

        $this->actingAs($admin)->post(route('admin.packages.toggle', $flash));
        $this->assertFalse($flash->fresh()->is_active);

        $this->actingAs($admin)->delete(route('admin.packages.destroy', $flash));
        $this->assertSoftDeleted($flash);
        $this->assertSame(6, Package::count());

        $this->actingAs($admin)->post(route('admin.packages.restore', $flash->id));
        $this->assertNotSoftDeleted($flash);
    }

    public function test_package_validation_rejects_invalid_values(): void
    {
        $this->actingAs($this->admin())->post(route('admin.packages.store'), ['name' => '', 'price' => -5, 'duration_minutes' => 5, 'sort_order' => 1])
            ->assertSessionHasErrors(['name', 'price', 'duration_minutes']);
    }
}
