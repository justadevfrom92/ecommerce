<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Support\DataTable;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Category::query()->with('department')->withCount('products');

        if ($department = $request->query('department')) {
            $query->where('department_id', $department);
        }

        $categories = DataTable::paginate($query, $request,
            searchable: ['name', 'slug', 'department.name'],
            sortable: ['name', 'sort_order', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc',
        );

        return view('admin.categories.index', [
            'categories' => $categories,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_active' => true, 'sort_order' => 0, 'department_id' => $request->query('department')]),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Slug::unique(Category::class, ($data['slug'] ?? null) ?: $data['name']);
        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['category' => $category, 'departments' => Department::orderBy('name')->get()]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Slug::unique(Category::class, ($data['slug'] ?? null) ?: $data['name'], $category->id);
        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category saved.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', "{$category->name} still has products. Move or delete them first.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', "Deleted {$category->name}.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
