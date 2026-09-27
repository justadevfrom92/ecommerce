<x-layouts.admin title="Products">
    @php
        $tab = request('status') ?: (request('stock') ?: 'all');
        $tabUrl = fn (array $q) => route('admin.products.index', $q);
    @endphp
    <x-slot:actions>
        @can('products.manage')
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add product</a>
        @endcan
    </x-slot:actions>

    <x-data-table :rows="$products" search-placeholder="Search products" label="products" :keep="['status', 'stock']" :columns="[
        'image' => ['label' => '', 'class' => 'ps-0'],
        'name' => ['label' => 'Product name', 'sortable' => true],
        'price' => ['label' => 'Price', 'sortable' => true, 'class' => 'text-end'],
        'category' => ['label' => 'Category'],
        'stock' => ['label' => 'Stock', 'sortable' => true, 'class' => 'text-end'],
        'status' => ['label' => 'Status'],
        'created_at' => ['label' => 'Published on', 'sortable' => true],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:tabs>
            <a href="{{ $tabUrl([]) }}" @class(['active' => $tab === 'all'])>All <span class="count">({{ number_format($counts['all']) }})</span></a>
            <a href="{{ $tabUrl(['status' => 'active']) }}" @class(['active' => $tab === 'active'])>Active <span class="count">({{ number_format($counts['active']) }})</span></a>
            <a href="{{ $tabUrl(['status' => 'inactive']) }}" @class(['active' => $tab === 'inactive'])>Hidden <span class="count">({{ number_format($counts['inactive']) }})</span></a>
            <a href="{{ $tabUrl(['stock' => 'low']) }}" @class(['active' => $tab === 'low'])>Low stock <span class="count">({{ number_format($counts['low']) }})</span></a>
            <a href="{{ $tabUrl(['stock' => 'out']) }}" @class(['active' => $tab === 'out'])>Out of stock <span class="count">({{ number_format($counts['out']) }})</span></a>
        </x-slot:tabs>
        <x-slot:filters>
            <select name="department" class="form-select form-select-sm w-auto" aria-label="Filter by department" data-auto-submit>
                <option value="">All departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" @selected((string) request('department') === (string) $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </x-slot:filters>

        @foreach ($products as $product)
            <tr>
                <td style="width: 4.5rem"><img src="{{ $product->imageUrl() }}" alt="" class="thumb"></td>
                <td style="min-width: 14rem">
                    @can('products.manage')
                        <a href="{{ route('admin.products.edit', $product) }}" class="table-link">{{ $product->name }}</a>
                    @else
                        <span class="fw-semibold">{{ $product->name }}</span>
                    @endcan
                    <div class="small text-muted-2">{{ $product->sku }}</div>
                </td>
                <td class="text-end nowrap tabular">
                    <span class="fw-bold" style="color: var(--ms-heading)">{{ money($product->price) }}</span>
                    @if ($product->isOnSale())<div class="small price-old">{{ money($product->compare_at_price) }}</div>@endif
                </td>
                <td class="text-muted-2">{{ $product->category->name }}<div class="small">{{ $product->category->department->name }}</div></td>
                <td class="text-end tabular">
                    <span class="{{ $product->stock === 0 ? 'pill pill-danger' : ($product->stock <= 5 ? 'pill pill-warning' : 'fw-bold') }}">{{ $product->stock }}</span>
                </td>
                <td>
                    <span class="{{ $product->is_active ? 'pill pill-success' : 'pill pill-neutral' }}">{{ $product->is_active ? 'Active' : 'Hidden' }}</span>
                    @if ($product->is_featured)<span class="pill pill-info ms-1">Featured</span>@endif
                </td>
                <td class="text-muted-2 nowrap">{{ $product->created_at->format('M j, g:i A') }}</td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-link text-muted-2 p-1" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for {{ $product->name }}"><i class="bi bi-three-dots"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="{{ route('products.show', $product) }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-2"></i>View in store</a></li>
                            @can('products.manage')
                                <li><a class="dropdown-item" href="{{ route('admin.products.edit', $product) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Delete {{ $product->name }}? Past orders keep their copy of it.">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                    </form>
                                </li>
                            @endcan
                        </ul>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
