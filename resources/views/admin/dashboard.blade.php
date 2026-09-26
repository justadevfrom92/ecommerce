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

        @if ($toFulfil || $awaitingPayment)
            <div class="d-flex flex-wrap gap-2 mb-4">
                @if ($toFulfil)
                    <a href="{{ route('admin.orders.index', ['status' => 'paid']) }}" class="btn btn-sm btn-primary"><i class="bi bi-box me-1"></i>{{ $toFulfil }} {{ Str::plural('order', $toFulfil) }} to fulfil</a>
                @endif
                @if ($awaitingPayment)
                    <a href="{{ route('admin.orders.index', ['status' => 'pending_payment']) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-hourglass-split me-1"></i>{{ $awaitingPayment }} awaiting payment</a>
                @endif
            </div>
        @endif

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
                        <h2 class="h6 mb-0">Recent orders</h2>
                        @can('orders.view')<a href="{{ route('admin.orders.index') }}" class="small">View all</a>@endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr>
                                <th class="small text-uppercase text-body-secondary">Order</th>
                                <th class="small text-uppercase text-body-secondary">Customer</th>
                                <th class="small text-uppercase text-body-secondary">Status</th>
                                <th class="small text-uppercase text-body-secondary text-end">Total</th>
                            </tr></thead>
                            <tbody>
                                @forelse ($recentOrders as $order)
                                    <tr>
                                        <td>@can('orders.view')<a href="{{ route('admin.orders.show', $order) }}">{{ $order->number }}</a>@else {{ $order->number }} @endcan
                                            <div class="small text-body-secondary">{{ $order->created_at->diffForHumans() }}</div></td>
                                        <td>{{ $order->shipping_name }}</td>
                                        <td><span class="badge {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span></td>
                                        <td class="text-end">{{ money($order->total) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-body-secondary py-4">No orders yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 d-flex flex-column gap-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body py-3"><h2 class="h6 mb-0">Top sellers (30 days)</h2></div>
                    <ul class="list-group list-group-flush small">
                        @forelse ($topProducts as $row)
                            <li class="list-group-item d-flex justify-content-between gap-2">
                                <span class="text-truncate">{{ $row->name }}</span>
                                <span class="text-nowrap text-body-secondary">{{ $row->units }} sold</span>
                            </li>
                        @empty
                            <li class="list-group-item text-body-secondary">No sales yet.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
                        <h2 class="h6 mb-0">Low stock</h2>
                        @can('products.view')<a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="small">View all</a>@endcan
                    </div>
                    <ul class="list-group list-group-flush small">
                        @forelse ($lowStock as $product)
                            <li class="list-group-item d-flex justify-content-between gap-2">
                                <span class="text-truncate">{{ $product->name }}</span>
                                <span class="badge {{ $product->stock === 0 ? 'text-bg-danger' : 'text-bg-warning' }}">{{ $product->stock }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-body-secondary">Everything is well stocked.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    @endif
</x-layouts.admin>
