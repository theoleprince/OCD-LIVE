<?php

use Illuminate\Support\Facades\Schedule;

// Libère les créneaux bloqués sans paiement au-delà du délai configuré.
// Nécessite un service « cron » Railway exécutant : php artisan schedule:run (voir README).
Schedule::command('ocd:expire-unpaid')->hourly()->withoutOverlapping();
