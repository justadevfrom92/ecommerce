<x-layouts.admin title="Dashboard">
    @if ($stats === null)
        <div class="card border-0 shadow-sm"><div class="card-body p-4">
            <h2 class="h5">Welcome, {{ auth()->user()->name }}</h2>
            <p class="text-body-secondary mb-0">Use the menu to get to the sections your role can access.</p>
        </div></div>
    @else
    <div class="row g-3 mb-4">
        @foreach ($stats as $stat)
            <div class="col-sm-6 col-xl-3">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="stat-icon bg-{{ $stat['color'] }}-subtle text-{{ $stat['color'] }}-emphasis"><i class="bi bi-{{ $stat['icon'] }}"></i></span>
                        <div>
                            <div class="small text-body-secondary">{{ $stat['label'] }}</div>
                            <div class="fs-4 fw-bold">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
            <h2 class="h6 mb-0">Low stock</h2>
            @can('products.view')
                <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="small">View all</a>
            @endcan
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th class="small text-uppercase text-body-secondary">Product</th><th class="small text-uppercase text-body-secondary">Category</th><th class="small text-uppercase text-body-secondary text-end">Stock</th></tr></thead>
                <tbody>
                    @forelse ($lowStock as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td class="text-body-secondary">{{ $product->category->name }}</td>
                            <td class="text-end"><span class="badge {{ $product->stock === 0 ? 'text-bg-danger' : 'text-bg-warning' }}">{{ $product->stock }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-body-secondary py-4">Everything is well stocked.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-layouts.admin>
