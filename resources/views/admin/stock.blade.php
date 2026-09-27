<x-layouts.admin title="Low stock">
    <x-data-table :rows="$products" search-placeholder="Search products" label="products" empty="Nothing is running low." :columns="[
        'image' => ['label' => ''],
        'name' => ['label' => 'Product', 'sortable' => true],
        'category' => ['label' => 'Category'],
        'stock' => ['label' => 'Stock', 'sortable' => true, 'class' => 'text-end'],
        'status' => ['label' => 'Status'],
        'updated_at' => ['label' => 'Last updated', 'sortable' => true],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
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
                <td class="text-muted-2">{{ $product->category->name }}<div class="small">{{ $product->category->department->name }}</div></td>
                <td class="text-end tabular"><span class="pill {{ $product->stock === 0 ? 'pill-danger' : 'pill-warning' }}">{{ $product->stock === 0 ? 'Out of stock' : $product->stock.' left' }}</span></td>
                <td><span class="pill {{ $product->is_active ? 'pill-success' : 'pill-neutral' }}">{{ $product->is_active ? 'Active' : 'Hidden' }}</span></td>
                <td class="text-muted-2 nowrap">{{ $product->updated_at->format('M j, g:i A') }}</td>
                <td class="text-end">
                    @can('products.manage')
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-soft btn-sm">Restock</a>
                    @endcan
                </td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
