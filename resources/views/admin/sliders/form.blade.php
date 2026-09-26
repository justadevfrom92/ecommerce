@php($editing = $slider->exists)
<x-layouts.admin :title="$editing ? 'Edit slider' : 'Add slider'">
    <div class="card border-0 shadow-sm" style="max-width: 44rem">
        <div class="card-body p-4">
            <form method="POST" action="{{ $editing ? route('admin.sliders.update', $slider) : route('admin.sliders.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <x-form.input name="title" label="Title" :value="$slider->title" required maxlength="100" />
                <x-form.select name="source" label="Products to show" :options="\App\Models\HomeSlider::SOURCES" :value="$slider->source" required />
                <x-form.select name="department_id" label="Department" placeholder="—" :options="$departments->pluck('name', 'id')->all()"
                    :value="$slider->source === 'department' ? $slider->source_id : null" help="Used when “A department” is chosen." />
                <x-form.select name="category_id" label="Category" placeholder="—"
                    :options="$categories->groupBy(fn ($c) => $c->department->name)->map(fn ($g) => $g->pluck('name', 'id')->all())->all()"
                    :value="$slider->source === 'category' ? $slider->source_id : null" help="Used when “A category” is chosen." />
                <div class="row">
                    <x-form.input class="col-6" name="max_items" label="Max products" type="number" min="4" max="30" :value="$slider->max_items" required />
                    <x-form.input class="col-6" name="sort_order" label="Sort order" type="number" min="0" :value="$slider->sort_order" required />
                </div>
                <x-form.check name="is_active" label="Show on homepage" :checked="$slider->is_active" />
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $editing ? 'Save' : 'Add slider' }}</button>
                    <a href="{{ route('admin.sliders.index') }}" class="btn btn-link">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
