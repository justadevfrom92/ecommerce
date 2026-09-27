<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Product;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function show(Department $department): View
    {
        abort_unless($department->is_active, 404);

        $categories = $department->categories()->active()->get();

        // One slider per category, newest first.
        $sliders = $categories->map(fn ($category) => [
            'category' => $category,
            'products' => $category->products()->active()->inStock()->with('category')->latest()->limit(12)->get(),
        ])->filter(fn ($s) => $s['products']->isNotEmpty());

        $featured = Product::active()->inStock()->with('category')
            ->whereIn('category_id', $categories->pluck('id'))
            ->where('is_featured', true)
            ->latest()->limit(12)->get();

        return view('store.department', compact('department', 'categories', 'sliders', 'featured'));
    }
}
