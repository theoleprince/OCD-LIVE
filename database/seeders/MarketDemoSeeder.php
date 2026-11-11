<?php

namespace Database\Seeders;

use App\Enums\LiveSubmissionStatus;
use App\Models\Category;
use App\Models\Country;
use App\Models\LiveSchedule;
use App\Models\LiveSubmission;
use App\Models\Seller;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Exemples fictifs : deux mois d'historique et une sélection visible pendant deux mois. */
class MarketDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) && ! config('ocd.market_demo_enabled')) {
            $this->command?->warn('Démonstration Market désactivée : utiliser SEED_MARKET_DEMO=true pour l’activer explicitement sur un serveur.');

            return;
        }

        $this->call([CountrySeeder::class, CitySeeder::class, CategorySeeder::class, LiveScheduleSeeder::class]);
        $now = CarbonImmutable::now(config('ocd.timezone'));
        $duration = max(1, (int) setting('market_publication_minutes', 90));
        $sectors = [
            ['Mode et vêtements', 'Atelier des tissus', 'market-mode.webp', 'Découvrez nos robes, chemises et tissus africains, présentés en direct depuis notre boutique.'],
            ['Agriculture et produits locaux', 'Le panier du marché', 'market-produits-locaux.webp', 'Fruits de saison, légumes frais et produits locaux : découverte des arrivages depuis notre commerce.'],
            ['Téléphones et accessoires', 'Le comptoir connecté', 'market-electronique.webp', 'Présentation de casques, accessoires et équipements du quotidien depuis notre magasin.'],
            ['Artisanat et créations', 'Terre et créations', 'market-artisanat.webp', 'Vases, poteries et objets faits main : démonstration des créations depuis notre atelier.'],
        ];

        DB::transaction(function () use ($now, $duration, $sectors) {
            $sellers = [];
            $schedules = LiveSchedule::active()->ordered()->get();
            $countries = Country::whereIn('iso_code', ['TG', 'BJ', 'CI', 'SN', 'CM', 'GH'])->orderBy('iso_code')->get();
            $names = ['Afi Mensah', 'Aminata Diallo', 'Mariam Koné', 'Fatou Ndiaye', 'Estelle Mboa', 'Akosua Owusu'];

            foreach ($countries as $countryIndex => $country) {
                $city = $country->cities()->active()->orderBy('id')->firstOrFail();
                foreach ($sectors as $sectorIndex => [$categoryName, $business, $image, $description]) {
                    $key = strtolower($country->iso_code).'-'.$sectorIndex;
                    $seller = Seller::updateOrCreate(['public_slug' => 'demo-market-'.$key], [
                        'full_name' => $names[$countryIndex],
                        'business_name' => '[DÉMO] '.$business.' · '.$city->name,
                        'phone' => sprintf('+000%06d', $countryIndex * 4 + $sectorIndex + 1),
                        'country_id' => $country->id,
                        'city_id' => $city->id,
                        'description' => 'Commerce fictif de démonstration. '.$description,
                        'profile_image' => 'market/demo/'.$image,
                        'consents' => ['terms' => true, 'publication' => true, 'ownership' => true],
                    ]);
                    $sellers[] = [$key, $seller, Category::where('name', $categoryName)->firstOrFail(), $description];
                }
            }

            $save = function (string $reference, array $entry, CarbonImmutable $date, LiveSubmissionStatus $status) use ($duration, $schedules) {
                [$key, $seller, $category, $description] = $entry;
                $published = in_array($status, [LiveSubmissionStatus::Approved, LiveSubmissionStatus::Expired], true);
                LiveSubmission::updateOrCreate(['reference' => $reference], [
                    'tracking_token' => hash('sha256', 'ocd-market-demo:'.$reference),
                    'seller_id' => $seller->id,
                    'country_id' => $seller->country_id,
                    'city_id' => $seller->city_id,
                    'category_id' => $category->id,
                    'schedule_id' => $schedules[$seller->id % $schedules->count()]->id,
                    'platform' => 'tiktok',
                    'account_name' => '@demo_market_'.str_replace('-', '_', $key).($published ? '' : '_exemple'),
                    'live_url' => 'https://www.tiktok.com/live',
                    'product_description' => $description,
                    'consents' => $seller->consents,
                    'status' => $status,
                    'submitted_at' => $date->subMinutes(15),
                    'reviewed_at' => $status === LiveSubmissionStatus::Pending ? null : $date->subMinutes(5),
                    'approved_at' => $published ? $date : null,
                    'published_at' => $published ? $date : null,
                    'expires_at' => $published
                        ? (str_starts_with($reference, 'MKT-DEMO-NOW-') ? $date->addMonthsNoOverflow(2) : $date->addMinutes($duration))
                        : null,
                    'rejection_reason' => $status === LiveSubmissionStatus::Rejected ? 'Exemple : description à compléter.' : null,
                    'internal_comment' => 'Donnée fictive générée par MarketDemoSeeder. Aucun vrai compte TikTok ni notification.',
                    'created_at' => $date->subMinutes(15),
                    'updated_at' => $date,
                ]);
            };

            $dayIndex = 0;
            for ($day = $now->subMonthsNoOverflow(2)->startOfDay(); $day->lt($now->startOfDay()); $day = $day->addDay()) {
                for ($slot = 0; $slot < 4; $slot++) {
                    $entry = $sellers[($dayIndex * 4 + $slot) % count($sellers)];
                    $status = match (($dayIndex * 4 + $slot) % 12) {
                        0 => LiveSubmissionStatus::Rejected,
                        1 => LiveSubmissionStatus::Cancelled,
                        default => LiveSubmissionStatus::Expired,
                    };
                    $save('MKT-DEMO-'.$day->format('Ymd').'-'.$slot, $entry, $day->setTime(9 + $slot * 3, 0), $status);
                }
                $dayIndex++;
            }

            foreach ($sellers as $index => $entry) {
                $save('MKT-DEMO-NOW-'.$entry[0], $entry, $now, LiveSubmissionStatus::Approved);
                if ($index < 8) {
                    $save('MKT-DEMO-REVIEW-'.$entry[0], $entry, $now, $index % 2 ? LiveSubmissionStatus::UnderReview : LiveSubmissionStatus::Pending);
                }
            }
        });

        $this->command?->info('Market : 24 vendeurs fictifs, deux mois d’historique, 24 exemples visibles pendant deux mois et 8 demandes à modérer. Les vrais lives conservent leur durée normale.');
    }
}
