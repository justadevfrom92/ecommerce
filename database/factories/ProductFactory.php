<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title(fake()->words(fake()->numberBetween(2, 4), true));
        $price = fake()->randomFloat(2, 5, 500);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'sku' => strtoupper(Str::random(3)).'-'.fake()->unique()->numerify('#####'),
            'description' => fake()->paragraphs(2, true),
            'price' => $price,
            'compare_at_price' => fake()->boolean(20) ? round($price * fake()->randomFloat(2, 1.1, 1.6), 2) : null,
            'stock' => fake()->boolean(85) ? fake()->numberBetween(1, 200) : 0,
            'is_active' => true,
            'is_featured' => fake()->boolean(25),
        ];
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock' => 0]);
    }
}
