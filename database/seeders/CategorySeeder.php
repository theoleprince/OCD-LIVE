<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Les 16 catégories commerciales initiales (cahier des charges §6.4). Idempotent par slug. */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::categories() as $i => $name) {
            Category::withTrashed()->firstOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    public static function categories(): array
    {
        return [
            'Alimentation et gastronomie',
            'Mode et vêtements',
            'Chaussures et maroquinerie',
            'Téléphones et accessoires',
            'Informatique et électronique',
            'Beauté et cosmétiques',
            'Maison et décoration',
            'Mobilier et ameublement',
            'Agriculture et produits locaux',
            'Immobilier et construction',
            'Automobile et transport',
            'Services professionnels',
            'Artisanat et créations',
            'Éducation et formation',
            'Santé et bien-être',
            'Autres activités',
        ];
    }
}
