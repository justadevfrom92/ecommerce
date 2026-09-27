<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load('category.department');

        $related = Product::active()->inStock()->with('category')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->inRandomOrder()->limit(12)->get();

        $departmentPicks = Product::active()->inStock()->with('category')
            ->whereHas('category', fn ($q) => $q->where('department_id', $product->category->department_id)->where('id', '!=', $product->category_id))
            ->where('is_featured', true)
            ->latest()->limit(12)->get();

        return view('store.product', compact('product', 'related', 'departmentPicks'));
    }
}
