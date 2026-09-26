<x-layouts.admin title="Categories">
    <x-data-table :rows="$categories" search-placeholder="Search categories…" label="categories" :columns="[
        'name' => ['label' => 'Category', 'sortable' => true],
        'department' => ['label' => 'Department'],
        'products' => ['label' => 'Products', 'class' => 'text-end'],
        'sort_order' => ['label' => 'Order', 'sortable' => true, 'class' => 'text-end'],
        'status' => ['label' => 'Status'],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:filters>
            <select name="department" class="form-select form-select-sm w-auto" aria-label="Filter by department" data-auto-submit>
                <option value="">All departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected((string) request('department') === (string) $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </x-slot:filters>
        <x-slot:toolbar>
            <a href="{{ route('admin.categories.create', ['department' => request('department')]) }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add category</a>
        </x-slot:toolbar>
        @foreach ($categories as $category)
            <tr>
                <td><div class="fw-semibold">{{ $category->name }}</div><div class="small text-body-secondary">/categories/{{ $category->slug }}</div></td>
                <td>{{ $category->department->name }}</td>
                <td class="text-end">
                    @can('products.view')
                        <a href="{{ route('admin.products.index', ['search' => $category->name]) }}">{{ $category->products_count }}</a>
                    @else
                        {{ $category->products_count }}
                    @endcan
                </td>
                <td class="text-end">{{ $category->sort_order }}</td>
                <td><span class="badge {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $category->is_active ? 'Active' : 'Hidden' }}</span></td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary" aria-label="Edit {{ $category->name }}"><i class="bi bi-pencil"></i></a>
                    <x-delete-button :action="route('admin.categories.destroy', $category)" icon-only :label="'Delete '.$category->name" :confirm="'Delete '.$category->name.'?'" />
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
