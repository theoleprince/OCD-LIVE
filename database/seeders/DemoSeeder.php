<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\ParticipationMode;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Données FICTIVES de démonstration, toutes marquées is_demo = true et préfixées « [DÉMO] ».
 * Aucun paiement encaissé n'est créé : les revenus du tableau de bord restent à zéro.
 * Suppression : php artisan ocd:purge-demo
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Customer::where('is_demo', true)->exists()) {
            $this->command?->info('Données de démonstration déjà présentes : rien à faire.');

            return;
        }

        $tz = config('ocd.timezone');
        $packages = Package::pluck('id', 'slug');

        $entries = [
            ['Dupont', 'Jean', 'Cameroun', 'Douala', 'Africa/Douala', '[DÉMO] Saveurs de Douala', 'Alimentation & restauration', 'REMOTE', 'starter-live', '2026-12-01 10:00', BookingStatus::PendingPayment, true],
            ['Koffi', 'Ama', 'Togo', 'Lomé', 'Africa/Lome', '[DÉMO] Atelier Pagne Ama', 'Mode & textile', 'ONSITE', 'business-live', '2026-12-02 14:00', BookingStatus::PendingPayment, false],
            ['Diop', 'Mamadou', 'Sénégal', 'Dakar', 'Africa/Dakar', '[DÉMO] Dakar Beauté Naturelle', 'Beauté & cosmétique', 'REMOTE', 'business-premium', '2026-12-03 09:00', BookingStatus::PendingPayment, false],
            ['Mahamat', 'Achta', 'Tchad', 'N\'Djamena', 'Africa/Ndjamena', '[DÉMO] Artisanat du Sahel', 'Artisanat & décoration', 'REMOTE', 'starter-live', '2026-12-04 16:00', BookingStatus::Cancelled, true],
        ];

        foreach ($entries as $i => [$last, $first, $country, $city, $ptz, $bizName, $sector, $mode, $slug, $start, $status, $assist]) {
            $customer = Customer::create([
                'last_name' => $last, 'first_name' => $first,
                'email' => 'demo'.($i + 1).'@exemple.invalid',
                'phone' => '+000 00 00 0'.($i + 1), 'whatsapp' => '+000 00 00 0'.($i + 1),
                'country' => $country, 'city' => $city, 'timezone' => $ptz, 'is_demo' => true,
            ]);

            $business = $customer->businesses()->create([
                'name' => $bizName, 'sector' => $sector,
                'description' => 'Entreprise fictive créée pour la démonstration de la plateforme OCD Live.',
                'offer_nature' => 'Produits de démonstration',
                'business_phone' => '+000 00 00 0'.($i + 1), 'business_address' => $city.' (adresse fictive)',
                'is_food' => in_array($sector, config('ocd.food_sectors'), true),
                'food_preparation' => $sector === 'Alimentation & restauration' ? 'Préparé le matin même (exemple).' : null,
                'food_conservation' => $sector === 'Alimentation & restauration' ? 'Glacière, consommation sous 24 h (exemple).' : null,
                'is_demo' => true,
            ]);
            $business->products()->createMany([
                ['name' => 'Produit démo A', 'quantity_available' => 20],
                ['name' => 'Produit démo B', 'quantity_available' => 10],
            ]);

            $package = Package::find($packages[$slug]);
            $startsAt = CarbonImmutable::parse($start, $tz);

            $booking = Booking::create([
                'reference' => 'TMP-DEMO-'.$i,
                'customer_id' => $customer->id, 'business_id' => $business->id, 'package_id' => $package->id,
                'mode' => ParticipationMode::from($mode), 'booking_date' => $startsAt->toDateString(),
                'starts_at' => $startsAt, 'ends_at' => $startsAt->addMinutes($package->duration_minutes),
                'duration_minutes' => $package->duration_minutes, 'amount' => $package->price, 'currency' => 'XOF',
                'status' => $status,
                'participant_timezone' => $ptz,
                'connection_type' => $mode === 'REMOTE' ? '4g' : null,
                'needs_assistance' => $mode === 'REMOTE' && $assist,
                'assistance_notes' => $mode === 'REMOTE' && $assist ? 'Besoin d\'aide pour le cadrage (exemple).' : null,
                'products_count' => 2,
                'cancelled_at' => $status === BookingStatus::Cancelled ? now() : null,
                'cancellation_reason' => $status === BookingStatus::Cancelled ? 'Annulation fictive (démonstration)' : null,
                'payment_due_at' => now()->addYear(),
                'is_demo' => true,
            ]);
            $booking->update(['reference' => sprintf('OCD-DEMO-%04d', $booking->id)]);

            $booking->payments()->create([
                'provider' => 'manual', 'amount' => $booking->amount, 'currency' => 'XOF',
                'status' => $status === BookingStatus::Cancelled ? PaymentStatus::Failed : PaymentStatus::Pending,
            ]);
        }

        $this->command?->info(count($entries).' réservations de démonstration créées (aucun paiement encaissé).');
    }
}
