<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'eu_size' => $this->faker->numberBetween(39, 46),
            'stock' => $this->faker->numberBetween(0, 40),
        ];
    }
}
