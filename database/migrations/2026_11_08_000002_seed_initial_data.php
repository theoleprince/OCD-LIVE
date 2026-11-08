<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * Données initiales au premier déploiement (l'hébergeur n'exécute que les migrations) :
 * les 6 packages, le compte administrateur (ADMIN_EMAIL / ADMIN_PASSWORD) et, si SEED_DEMO=true,
 * les données de démonstration. Les seeders sont idempotents : rien d'existant n'est écrasé.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        Artisan::call('db:seed', ['--force' => true]);
    }

    public function down(): void
    {
        // Rien à annuler : les données saisies ou modifiées depuis sont conservées.
    }
};
