<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use App\Support\DataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->with('category.department');

        if ($department = $request->query('department')) {
            $query->whereHas('category', fn ($q) => $q->where('department_id', $department));
        }

        match ($request->query('stock')) {
            'out' => $query->where('stock', 0),
            'low' => $query->whereBetween('stock', [1, 5]),
            default => null,
        };

        if (in_array($request->query('status'), ['active', 'inactive'], true)) {
            $query->where('is_active', $request->query('status') === 'active');
        }

        $products = DataTable::paginate($query, $request,
            searchable: ['name', 'sku', 'category.name'],
            sortable: ['name', 'sku', 'price', 'stock', 'created_at'],
            defaultSort: 'created_at',
        );

        return view('admin.products.index', [
            'products' => $products,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }
}
