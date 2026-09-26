<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Support\DataTable;
use App\Support\Slug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(Request $request): View
    {
        $departments = DataTable::paginate(
            Department::query()->withCount(['categories', 'products']),
            $request,
            searchable: ['name', 'slug'],
            sortable: ['name', 'sort_order', 'created_at'],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
        );

        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('admin.departments.form', ['department' => new Department(['is_active' => true, 'sort_order' => Department::max('sort_order') + 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Slug::unique(Department::class, ($data['slug'] ?? null) ?: $data['name']);
        Department::create($data);
        Cache::forget('nav.departments');

        return redirect()->route('admin.departments.index')->with('status', 'Department created.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.form', compact('department'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Slug::unique(Department::class, ($data['slug'] ?? null) ?: $data['name'], $department->id);
        $department->update($data);
        Cache::forget('nav.departments');

        return redirect()->route('admin.departments.index')->with('status', 'Department saved.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->products()->exists()) {
            return back()->with('error', "{$department->name} still has products. Move or delete them first.");
        }

        $department->delete();
        Cache::forget('nav.departments');

        return redirect()->route('admin.departments.index')->with('status', "Deleted {$department->name}.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
