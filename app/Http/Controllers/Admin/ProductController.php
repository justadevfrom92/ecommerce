<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use App\Support\DataTable;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
            'counts' => [
                'all' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'inactive' => Product::where('is_active', false)->count(),
                'low' => Product::whereBetween('stock', [1, 5])->count(),
                'out' => Product::where('stock', 0)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', ['product' => new Product(['is_active' => true]), 'departments' => $this->departments()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Slug::unique(Product::class, ($data['slug'] ?? null) ?: $data['name']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', ['product' => $product, 'departments' => $this->departments()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);
        $data['slug'] = Slug::unique(Product::class, ($data['slug'] ?? null) ?: $data['name'], $product->id);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->hasFile('image') ? $request->file('image')->store('products', 'public') : null;
        }

        $product->update($data);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', "Deleted {$product->name}.");
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'sku' => ['required', 'string', 'max:64', Rule::unique('products')->ignore($product?->id)],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:20000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'compare_at_price' => ['nullable', 'numeric', 'gt:price', 'max:99999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        unset($data['image'], $data['remove_image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }

    private function departments()
    {
        return Department::with('categories')->orderBy('sort_order')->orderBy('name')->get();
    }
}
