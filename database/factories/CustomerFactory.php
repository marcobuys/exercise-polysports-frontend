<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => $this->faker->unique()->company(),
            'email' => $this->faker->unique()->companyEmail(),
            'tier' => $this->faker->randomElement(['bronze', 'silver', 'gold']),
        ];
    }
}
