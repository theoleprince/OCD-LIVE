<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Customer> */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+228 90 00 00 '.$this->faker->numberBetween(10, 99),
            'whatsapp' => '+228 90 00 00 '.$this->faker->numberBetween(10, 99),
            'country' => 'Togo',
            'city' => 'Lomé',
            'timezone' => 'Africa/Lome',
        ];
    }
}
