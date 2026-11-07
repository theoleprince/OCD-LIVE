<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Business> */
class BusinessFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => $this->faker->company(),
            'sector' => 'Mode & textile',
            'description' => 'Une activité de test pour la plateforme OCD Live.',
            'offer_nature' => 'Vêtements',
            'business_phone' => '+228 22 00 00 00',
            'business_address' => 'Lomé, Togo',
        ];
    }
}
