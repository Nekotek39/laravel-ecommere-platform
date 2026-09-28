<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => Str::ucfirst(fake()->words(3, true)),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 9.99, 499.99),
            'stock' => fake()->numberBetween(1, 50),
            'image' => null,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
