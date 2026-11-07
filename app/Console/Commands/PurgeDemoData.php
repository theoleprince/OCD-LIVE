<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeDemoData extends Command
{
    protected $signature = 'ocd:purge-demo {--force : Sans confirmation}';

    protected $description = 'Supprime définitivement toutes les données marquées comme démonstration (is_demo).';

    public function handle(): int
    {
        $count = Booking::withTrashed()->where('is_demo', true)->count();

        if (! $this->option('force') && ! $this->confirm("Supprimer définitivement $count réservation(s) de démonstration et les clients associés ?")) {
            return self::SUCCESS;
        }

        DB::transaction(function () {
            Booking::withTrashed()->where('is_demo', true)->forceDelete();
            Business::withTrashed()->where('is_demo', true)->forceDelete();
            Customer::withTrashed()->where('is_demo', true)->forceDelete();
        });

        $this->info('Données de démonstration supprimées.');

        return self::SUCCESS;
    }
}
