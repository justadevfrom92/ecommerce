<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Department;
use App\Models\HomeSlider;
use App\Support\DataTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeSliderController extends Controller
{
    public function index(Request $request): View
    {
        $sliders = DataTable::paginate(HomeSlider::query(), $request,
            searchable: ['title'],
            sortable: ['title', 'sort_order'],
            defaultSort: 'sort_order',
            defaultDirection: 'asc',
        );

        return view('admin.sliders.index', [
            'sliders' => $sliders,
            'departments' => Department::pluck('name', 'id'),
            'categories' => Category::pluck('name', 'id'),
        ]);
    }

    public function create(): View
    {
        return $this->form(new HomeSlider(['is_active' => true, 'max_items' => 12, 'sort_order' => HomeSlider::max('sort_order') + 1, 'source' => 'featured']));
    }

    public function store(Request $request): RedirectResponse
    {
        HomeSlider::create($this->validated($request));

        return redirect()->route('admin.sliders.index')->with('status', 'Slider added to the homepage.');
    }

    public function edit(HomeSlider $slider): View
    {
        return $this->form($slider);
    }

    public function update(Request $request, HomeSlider $slider): RedirectResponse
    {
        $slider->update($this->validated($request));

        return redirect()->route('admin.sliders.index')->with('status', 'Slider saved.');
    }

    public function destroy(HomeSlider $slider): RedirectResponse
    {
        $slider->delete();

        return redirect()->route('admin.sliders.index')->with('status', 'Slider removed.');
    }

    private function form(HomeSlider $slider): View
    {
        return view('admin.sliders.form', [
            'slider' => $slider,
            'departments' => Department::orderBy('name')->get(),
            'categories' => Category::with('department')->orderBy('name')->get(),
        ]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'source' => ['required', Rule::in(array_keys(HomeSlider::SOURCES))],
            'department_id' => ['nullable', 'required_if:source,department', 'exists:departments,id'],
            'category_id' => ['nullable', 'required_if:source,category', 'exists:categories,id'],
            'max_items' => ['required', 'integer', 'min:4', 'max:30'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        return [
            'title' => $data['title'],
            'source' => $data['source'],
            'source_id' => match ($data['source']) {
                'department' => $data['department_id'],
                'category' => $data['category_id'],
                default => null,
            },
            'max_items' => $data['max_items'],
            'sort_order' => $data['sort_order'],
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
