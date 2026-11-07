<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

/**
 * Les six packages commerciaux. Idempotent : n'écrase jamais un package
 * déjà présent (les modifications faites dans l'administration sont conservées).
 */
class PackageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::packages() as $i => $data) {
            Package::withTrashed()->firstOrCreate(['slug' => $data['slug']], [...$data, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }

    public static function packages(): array
    {
        return [
            [
                'slug' => 'starter-live',
                'name' => 'Starter Live',
                'tagline' => 'Le premier pas vers la visibilité',
                'price' => 15000,
                'price_validated' => true,
                'duration_minutes' => 90,
                'max_products' => 3,
                'features' => [
                    'Présentation du produit ou service',
                    'Diffusion sur le compte House of Challenge',
                    'Affichage du numéro de téléphone',
                    'Présentation de l\'adresse commerciale',
                    'Accompagnement technique',
                ],
            ],
            [
                'slug' => 'business-live',
                'name' => 'Business Live',
                'tagline' => 'Pour présenter toute votre gamme',
                'price' => 45000,
                'duration_minutes' => 240,
                'is_featured' => true,
                'features' => [
                    'Présentation approfondie',
                    'Plusieurs produits',
                    'Diffusion en direct',
                    'Mise en avant des coordonnées',
                    'Assistance technique',
                ],
            ],
            [
                'slug' => 'business-premium',
                'name' => 'Business Premium',
                'tagline' => 'Une émission commerciale complète',
                'price' => 75000,
                'duration_minutes' => 360,
                'features' => [
                    'Présentation commerciale approfondie',
                    'Mise en avant de plusieurs produits',
                    'Présentation des coordonnées',
                    'Promotion préalable du passage',
                    'Assistance renforcée',
                ],
            ],
            [
                'slug' => 'visibility-plus',
                'name' => 'Visibility Plus',
                'tagline' => 'Live + contenus promotionnels',
                'price' => 120000,
                'duration_minutes' => 120,
                'duration_label' => 'Session + contenus',
                'features' => [
                    'Une session commerciale',
                    'Une vidéo promotionnelle',
                    'Publications promotionnelles',
                    'Mise en avant de l\'entreprise',
                ],
                'terms' => 'Nombre de publications, format et délai de livraison de la vidéo précisés à la confirmation.',
            ],
            [
                'slug' => 'influence-boost',
                'name' => 'Influence Boost',
                'tagline' => 'La puissance d\'une campagne',
                'price' => 200000,
                'duration_minutes' => 120,
                'duration_label' => 'Session + campagne',
                'features' => [
                    'Une session commerciale',
                    'Campagne promotionnelle',
                    'Annonce par Bouvane sous réserve de disponibilité',
                    'Relais promotionnel sur les réseaux convenus',
                ],
                'terms' => 'Annonce de Bouvane sous réserve de disponibilité. Réseaux de relais et durée de la campagne définis d\'un commun accord.',
            ],
            [
                'slug' => 'partner-of-the-month',
                'name' => 'Partner of the Month',
                'tagline' => 'Un mois de présence régulière',
                'price' => 500000,
                'duration_minutes' => 120,
                'duration_label' => 'Un mois',
                'features' => [
                    'Plusieurs sessions selon calendrier contractuel',
                    'Promotion mensuelle',
                    'Vidéos promotionnelles',
                    'Mise en avant régulière',
                    'Accompagnement commercial',
                    'Possibilité d\'intervention de Bouvane selon contrat',
                ],
                'terms' => 'La réservation porte sur la première session. Les sessions suivantes sont planifiées selon le calendrier contractuel signé avec OCD Live.',
            ],
        ];
    }
}
