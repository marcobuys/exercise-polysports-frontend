<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'sku' => strtoupper($this->faker->unique()->bothify('???-####')),
            'category' => $this->faker->randomElement(['FG', 'AG', 'IN']),
            'colorway' => $this->faker->safeColorName().'/'.$this->faker->safeColorName(),
            'base_price_cents' => $this->faker->numberBetween(5000, 25000),
        ];
    }
}
