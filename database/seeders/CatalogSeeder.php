<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Demo departments, categories and products for the placeholder store. */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Electronics' => ['Phones', 'Laptops', 'Audio', 'Cameras'],
            'Clothing' => ['Men', 'Women', 'Kids', 'Shoes'],
            'Home & Kitchen' => ['Furniture', 'Cookware', 'Bedding', 'Decor'],
            'Sports & Outdoors' => ['Fitness', 'Camping', 'Cycling', 'Team Sports'],
        ];

        $order = 0;
        foreach ($catalog as $departmentName => $categories) {
            $department = Department::firstOrCreate(
                ['slug' => Str::slug($departmentName)],
                ['name' => $departmentName, 'description' => "Shop the best in {$departmentName}.", 'sort_order' => $order++],
            );

            foreach ($categories as $i => $categoryName) {
                $category = Category::firstOrCreate(
                    ['slug' => Str::slug($departmentName.' '.$categoryName)],
                    ['department_id' => $department->id, 'name' => $categoryName, 'sort_order' => $i],
                );

                if ($category->products()->doesntExist()) {
                    Product::factory()->count(14)->create(['category_id' => $category->id]);
                }
            }
        }
    }
}
