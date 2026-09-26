@php($editing = $department->exists)
<x-layouts.admin :title="$editing ? 'Edit department' : 'Add department'">
    <div class="card border-0 shadow-sm" style="max-width: 44rem">
        <div class="card-body p-4">
            <form method="POST" action="{{ $editing ? route('admin.departments.update', $department) : route('admin.departments.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <x-form.input name="name" label="Name" :value="$department->name" required />
                <x-form.input name="slug" label="URL slug" :value="$department->slug" help="Leave blank to generate it from the name." />
                <x-form.textarea name="description" label="Description" :value="$department->description" rows="3" />
                <x-form.input name="sort_order" label="Sort order" type="number" min="0" :value="$department->sort_order" required help="Lower numbers show first in the menu." />
                <x-form.check name="is_active" label="Active (shown in store)" :checked="$department->is_active" />
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $editing ? 'Save' : 'Create' }}</button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-link">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
