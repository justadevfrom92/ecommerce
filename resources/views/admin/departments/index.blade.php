<x-layouts.admin title="Departments">
    <x-data-table :rows="$departments" search-placeholder="Search departments…" label="departments" :columns="[
        'name' => ['label' => 'Department', 'sortable' => true],
        'categories' => ['label' => 'Categories', 'class' => 'text-end'],
        'products' => ['label' => 'Products', 'class' => 'text-end'],
        'sort_order' => ['label' => 'Order', 'sortable' => true, 'class' => 'text-end'],
        'status' => ['label' => 'Status'],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:toolbar>
            <a href="{{ route('admin.departments.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add department</a>
        </x-slot:toolbar>
        @foreach ($departments as $department)
            <tr>
                <td><div class="fw-semibold">{{ $department->name }}</div><div class="small text-body-secondary">/departments/{{ $department->slug }}</div></td>
                <td class="text-end">
                    @can('categories.manage')
                        <a href="{{ route('admin.categories.index', ['department' => $department->id]) }}">{{ $department->categories_count }}</a>
                    @else
                        {{ $department->categories_count }}
                    @endcan
                </td>
                <td class="text-end">{{ $department->products_count }}</td>
                <td class="text-end">{{ $department->sort_order }}</td>
                <td><span class="badge {{ $department->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $department->is_active ? 'Active' : 'Hidden' }}</span></td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-sm btn-outline-secondary" aria-label="Edit {{ $department->name }}"><i class="bi bi-pencil"></i></a>
                    <x-delete-button :action="route('admin.departments.destroy', $department)" icon-only :label="'Delete '.$department->name" :confirm="'Delete '.$department->name.' and its (empty) categories?'" />
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
