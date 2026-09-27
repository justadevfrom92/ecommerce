<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\DataTable;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Products running low (stock ≤ 5), out-of-stock first. */
class StockController extends Controller
{
    public const THRESHOLD = 5;

    public function __invoke(Request $request): View
    {
        $query = Product::query()->with('category.department')->where('stock', '<=', self::THRESHOLD);

        $products = DataTable::paginate($query, $request,
            searchable: ['name', 'sku', 'category.name'],
            sortable: ['stock', 'name', 'sku', 'updated_at'],
            defaultSort: 'stock',
            defaultDirection: 'asc',
        );

        return view('admin.stock', [
            'products' => $products,
            'outCount' => Product::where('stock', 0)->count(),
        ]);
    }
}
