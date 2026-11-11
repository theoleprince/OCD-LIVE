<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * php artisan db:seed            → packages + compte admin (si ADMIN_EMAIL) + démo (si SEED_DEMO=true)
     */
    public function run(): void
    {
        $this->call(PackageSeeder::class);

        // La migration historique des packages appelle ce seeder avant la création du Market.
        $marketReady = Schema::hasTable('live_reports');
        if ($marketReady) {
            $this->call(CountrySeeder::class);
            $this->call(CitySeeder::class);
            $this->call(CategorySeeder::class);
            $this->call(LiveScheduleSeeder::class);
        }

        if (config('ocd.admin.email')) {
            Artisan::call('ocd:create-admin');
            $this->command?->getOutput()->write(Artisan::output());
        } else {
            $this->command?->warn('ADMIN_EMAIL non défini : aucun compte administrateur créé (php artisan ocd:create-admin --email=...).');
        }

        $seedDemo = filter_var(env('SEED_DEMO', false), FILTER_VALIDATE_BOOL);
        if ($seedDemo) {
            $this->call(DemoSeeder::class);
        }
        if ($marketReady && ($seedDemo || config('ocd.market_demo_enabled'))) {
            $this->call(MarketDemoSeeder::class);
        }
    }
}
