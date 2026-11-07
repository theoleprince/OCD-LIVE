<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<\App\Models\Package> */
class PackageFactory extends Factory
{
    public function definition(): array
    {
        $name = 'Package '.Str::title($this->faker->unique()->word());

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'price' => 15000,
            'price_validated' => true,
            'duration_minutes' => 90,
            'features' => ['Présentation du produit'],
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
