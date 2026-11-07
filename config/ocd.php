<?php

return [

    'name' => 'OCD LIVE',
    'organization' => 'House of Challenge',
    'host' => 'Bouvane',
    'slogan' => 'Votre produit mérite d\'être vu.',
    'studio_city' => 'Lomé, Togo',
    'timezone' => 'Africa/Lome',
    'currency' => 'FCFA',
    'launch_date' => '2026-12-01',

    // Compte administrateur initial (voir php artisan ocd:create-admin).
    'admin' => [
        'name' => env('ADMIN_NAME', 'Administrateur OCD Live'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    // Valeurs par défaut des paramètres administrables (table settings).
    'defaults' => [
        'contact_phone' => '+228 00 00 00 00',
        'contact_whatsapp' => '22800000000',
        'contact_email' => 'contact@ocdlive.com',
        'contact_address' => 'Studio OCD Live, Lomé, Togo',
        'social_facebook' => '',
        'social_instagram' => '',
        'social_tiktok' => '',
        'social_youtube' => '',
        'opening_time' => '09:00',
        'closing_time' => '21:00',
        'open_weekdays' => '1,2,3,4,5,6', // ISO : 1 = lundi … 7 = dimanche
        'buffer_minutes' => '30',
        'slot_step_minutes' => '30',
        'booking_start_date' => '2026-12-01',
        'booking_horizon_days' => '120',
        'min_notice_hours' => '24',
        'payment_hold_hours' => '72',
        'admin_notification_email' => '',
    ],

    'uploads' => [
        'max_kb' => 4096,
        'max_product_photos' => 6,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    'sectors' => [
        'Alimentation & restauration',
        'Mode & textile',
        'Beauté & cosmétique',
        'Artisanat & décoration',
        'Électronique & high-tech',
        'Maison & ameublement',
        'Agriculture & produits locaux',
        'Santé & bien-être',
        'Services aux entreprises',
        'Formation & éducation',
        'Immobilier',
        'Événementiel',
        'Autre',
    ],

    // Secteurs déclenchant les questions spécifiques à l'alimentaire.
    'food_sectors' => ['Alimentation & restauration', 'Agriculture & produits locaux'],

    'connection_types' => [
        'fiber' => 'Fibre / ADSL',
        '4g' => '4G / 5G mobile',
        '3g' => '3G ou connexion instable',
        'unknown' => 'Je ne sais pas',
    ],

    // Pays proposés => fuseau horaire par défaut.
    'countries' => [
        'Togo' => 'Africa/Lome',
        'Bénin' => 'Africa/Porto-Novo',
        'Burkina Faso' => 'Africa/Ouagadougou',
        'Burundi' => 'Africa/Bujumbura',
        'Cameroun' => 'Africa/Douala',
        'Centrafrique' => 'Africa/Bangui',
        'Comores' => 'Indian/Comoro',
        'Congo' => 'Africa/Brazzaville',
        'Côte d\'Ivoire' => 'Africa/Abidjan',
        'Djibouti' => 'Africa/Djibouti',
        'Gabon' => 'Africa/Libreville',
        'Ghana' => 'Africa/Accra',
        'Guinée' => 'Africa/Conakry',
        'Guinée équatoriale' => 'Africa/Malabo',
        'Madagascar' => 'Indian/Antananarivo',
        'Mali' => 'Africa/Bamako',
        'Maroc' => 'Africa/Casablanca',
        'Mauritanie' => 'Africa/Nouakchott',
        'Niger' => 'Africa/Niamey',
        'Nigeria' => 'Africa/Lagos',
        'RD Congo' => 'Africa/Kinshasa',
        'Rwanda' => 'Africa/Kigali',
        'Sénégal' => 'Africa/Dakar',
        'Tchad' => 'Africa/Ndjamena',
        'Tunisie' => 'Africa/Tunis',
        'Algérie' => 'Africa/Algiers',
        'Belgique' => 'Europe/Brussels',
        'Canada' => 'America/Toronto',
        'États-Unis' => 'America/New_York',
        'France' => 'Europe/Paris',
        'Suisse' => 'Europe/Zurich',
        'Autre pays' => 'UTC',
    ],
];
