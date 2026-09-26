@php($editing = $category->exists)
<x-layouts.admin :title="$editing ? 'Edit category' : 'Add category'">
    <div class="card border-0 shadow-sm" style="max-width: 44rem">
        <div class="card-body p-4">
            <form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <x-form.select name="department_id" label="Department" :options="$departments->pluck('name', 'id')->all()" :value="$category->department_id" placeholder="Choose…" required />
                <x-form.input name="name" label="Name" :value="$category->name" required />
                <x-form.input name="slug" label="URL slug" :value="$category->slug" help="Leave blank to generate it from the name." />
                <x-form.textarea name="description" label="Description" :value="$category->description" rows="3" />
                <x-form.input name="sort_order" label="Sort order" type="number" min="0" :value="$category->sort_order" required />
                <x-form.check name="is_active" label="Active (shown in store)" :checked="$category->is_active" />
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $editing ? 'Save' : 'Create' }}</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-link">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
