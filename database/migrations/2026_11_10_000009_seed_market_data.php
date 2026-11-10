<?php

use Database\Seeders\CategorySeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\LiveScheduleSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Données de référence du module Market (pays, villes, catégories, créneaux) au déploiement :
 * l'hébergeur n'exécute que les migrations, pas db:seed. Seeders idempotents.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        foreach ([CountrySeeder::class, CitySeeder::class, CategorySeeder::class, LiveScheduleSeeder::class] as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }
    }

    public function down(): void
    {
        // Rien à annuler : les données saisies ou modifiées depuis sont conservées.
    }
};
