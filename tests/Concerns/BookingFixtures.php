<?php

namespace Tests\Concerns;

use App\Enums\UserRole;
use App\Models\Package;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PackageSeeder;

trait BookingFixtures
{
    protected function atLomeTime(string $at = '2026-11-20 10:00'): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse($at, 'Africa/Lome'));
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse($at, 'Africa/Lome'));
    }

    protected function seedPackages(): void
    {
        $this->seed(PackageSeeder::class);
    }

    protected function starter(): Package
    {
        return Package::where('slug', 'starter-live')->firstOrFail();
    }

    protected function admin(UserRole $role = UserRole::Admin): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role, 'is_active' => true])->save();

        return $user;
    }

    protected function bookingData(array $overrides = []): array
    {
        return array_replace_recursive([
            'mode' => 'REMOTE',
            'package_id' => $this->starter()->id,
            'starts_at' => '2026-12-01 10:00',
            'customer' => [
                'first_name' => 'Jean', 'last_name' => 'Dupont', 'email' => 'jean@example.com',
                'phone' => '+237 600 00 00 01', 'whatsapp' => '+237 600 00 00 01',
                'country' => 'Cameroun', 'city' => 'Douala', 'timezone' => 'Africa/Douala',
            ],
            'remote' => ['timezone' => 'Africa/Douala', 'connection_type' => '4g', 'needs_assistance' => true],
            'business' => [
                'name' => 'Commerce alimentaire', 'sector' => 'Alimentation & restauration',
                'description' => 'Vente de plats cuisinés camerounais.', 'offer_nature' => 'Plats, jus naturels',
                'business_phone' => '+237 600 00 00 02', 'business_address' => 'Akwa, Douala',
                'social_links' => ['facebook' => 'facebook.com/test'], 'products_count' => 2,
                'products' => [['name' => 'Ndolé', 'quantity' => 20], ['name' => 'Jus de bissap', 'quantity' => 50]],
                'food_preparation' => 'Préparé le matin', 'food_conservation' => 'Glacière',
            ],
        ], $overrides);
    }
}
