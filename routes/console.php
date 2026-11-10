<?php

use Illuminate\Support\Facades\Schedule;

// Libère les créneaux bloqués sans paiement au-delà du délai configuré.
// Nécessite un service « cron » Railway exécutant : php artisan schedule:run (voir README).
Schedule::command('ocd:expire-unpaid')->hourly()->withoutOverlapping();

// OCD LIVE MARKET : passe les publications dépassées en EXPIRED (durée configurable, ~90 min par défaut).
// Fréquence plus rapprochée que les réservations car la fenêtre de publication est courte.
Schedule::command('market:expire-lives')->everyFiveMinutes()->withoutOverlapping();
