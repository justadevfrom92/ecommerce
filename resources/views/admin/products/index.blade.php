<x-layouts.admin title="Products">
    <x-data-table :rows="$products" search-placeholder="Search name, SKU or category…" label="products" :columns="[
        'image' => ['label' => ''],
        'name' => ['label' => 'Product', 'sortable' => true],
        'sku' => ['label' => 'SKU', 'sortable' => true],
        'category' => ['label' => 'Category'],
        'price' => ['label' => 'Price', 'sortable' => true, 'class' => 'text-end'],
        'stock' => ['label' => 'Stock', 'sortable' => true, 'class' => 'text-end'],
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
            <select name="stock" class="form-select form-select-sm w-auto" aria-label="Filter by stock" data-auto-submit>
                <option value="">Any stock</option>
                <option value="low" @selected(request('stock') === 'low')>Low (1–5)</option>
                <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
            </select>
            <select name="status" class="form-select form-select-sm w-auto" aria-label="Filter by status" data-auto-submit>
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Hidden</option>
            </select>
        </x-slot:filters>
        @if (Route::has('admin.products.create'))
            <x-slot:toolbar>
                @can('products.manage')
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add product</a>
                @endcan
            </x-slot:toolbar>
        @endif

        @foreach ($products as $product)
            <tr>
                <td style="width: 56px"><img src="{{ $product->imageUrl() }}" alt="" class="table-thumb"></td>
                <td>
                    <div class="fw-semibold">{{ $product->name }}</div>
                    @if ($product->is_featured)<span class="badge text-bg-info-subtle text-info-emphasis small">Featured</span>@endif
                </td>
                <td class="text-body-secondary small">{{ $product->sku }}</td>
                <td class="small">{{ $product->category->name }} <span class="text-body-secondary">· {{ $product->category->department->name }}</span></td>
                <td class="text-end text-nowrap">
                    {{ money($product->price) }}
                    @if ($product->isOnSale())<div class="small text-body-secondary text-decoration-line-through">{{ money($product->compare_at_price) }}</div>@endif
                </td>
                <td class="text-end">
                    <span class="badge {{ $product->stock === 0 ? 'text-bg-danger' : ($product->stock <= 5 ? 'text-bg-warning' : 'text-bg-light border') }}">{{ $product->stock }}</span>
                </td>
                <td><span class="badge {{ $product->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $product->is_active ? 'Active' : 'Hidden' }}</span></td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" aria-label="View {{ $product->name }} in store"><i class="bi bi-box-arrow-up-right"></i></a>
                    @if (Route::has('admin.products.edit'))
                        @can('products.manage')
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary" aria-label="Edit {{ $product->name }}"><i class="bi bi-pencil"></i></a>
                        @endcan
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
