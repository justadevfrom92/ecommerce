<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'department_id' => Department::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => fake()->sentence(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
