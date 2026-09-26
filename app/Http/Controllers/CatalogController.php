<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Product grids: search results and category pages share filters, sorting and pagination. */
class CatalogController extends Controller
{
    public const SORTS = [
        'newest' => 'Newest',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
        'name' => 'Name A–Z',
    ];

    public function search(Request $request): View
    {
        $query = Product::active()->search($request->query('q'));

        if ($department = Department::active()->where('slug', $request->query('department'))->first()) {
            $query->whereHas('category', fn (Builder $q) => $q->where('department_id', $department->id));
        }

        return view('store.search', [
            'products' => $this->grid($query, $request),
            'departments' => Department::active()->orderBy('sort_order')->get(),
            'activeDepartment' => $department,
            'term' => trim((string) $request->query('q')),
        ]);
    }

    public function category(Request $request, Category $category): View
    {
        abort_unless($category->is_active && $category->department?->is_active, 404);

        $query = $category->products()->active()->search($request->query('q'));

        return view('store.category', [
            'category' => $category,
            'products' => $this->grid($query, $request),
            'siblings' => $category->department->categories()->active()->get(),
        ]);
    }

    private function grid(Builder|Relation $query, Request $request)
    {
        if ($request->boolean('on_sale')) {
            $query->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price');
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock', '>', 0);
        }

        match ($request->query('sort')) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return $query->with('category')->orderByDesc('id')->paginate(24)->withQueryString();
    }
}
