<x-layouts.admin :title="$stats ? 'Ecommerce Dashboard' : 'Dashboard'" :subtitle="$stats ? 'Here’s what’s going on at your business right now' : null">
    @if ($stats === null)
        <div class="panel panel-body">
            <h2 class="h5 fw-extrabold">Welcome, {{ auth()->user()->name }}</h2>
            <p class="text-muted-2 mb-0">Use the menu to get to the sections your role can access.</p>
        </div>
    @else
        {{-- Status flags --}}
        <div class="d-flex flex-wrap gap-4 gap-xl-5 pb-4 mb-4 border-bottom">
            @foreach ($flags as $flag)
                <a href="{{ $flag['url'] }}" class="dash-flag text-decoration-none">
                    <span class="flag-icon" style="background: {{ $flag['bg'] }}; color: {{ $flag['fg'] }}"><i class="bi bi-{{ $flag['icon'] }}"></i></span>
                    <span>
                        <span class="flag-num d-block">{{ number_format($flag['num']) }} {{ $flag['noun'] }}</span>
                        <span class="flag-label">{{ $flag['label'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Total sells --}}
        <div class="row g-4 mb-4">
            <div class="col-xxl-12">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-2">
                    <div>
                        <h2 class="section-title">Total sells</h2>
                        <p class="admin-subtitle">Payments received in the last 30 days</p>
                    </div>
                    <div class="text-end">
                        <div class="kpi-value">{{ money($revenue30) }}</div>
                        <div class="kpi-sub">{{ number_format($orders30) }} paid {{ Str::plural('order', $orders30) }}</div>
                    </div>
                </div>
                @include('admin._sales-chart', ['series' => $series])
            </div>
        </div>

        {{-- KPI cards --}}
        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <div class="panel panel-body h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="kpi-title">Total orders
                                @if ($kpis['orders']['change'] !== null)
                                    <span class="pill {{ $kpis['orders']['change'] >= 0 ? 'pill-success' : 'pill-warning' }} ms-1">{{ $kpis['orders']['change'] >= 0 ? '+' : '' }}{{ $kpis['orders']['change'] }}%</span>
                                @endif
                            </h3>
                            <div class="kpi-sub">Last 7 days</div>
                        </div>
                        <div class="kpi-value">{{ number_format($kpis['orders']['value']) }}</div>
                    </div>
                    <div class="meter mb-2" role="img" aria-label="{{ $kpis['orders']['paidPct'] }}% paid"><span style="width: {{ $kpis['orders']['paidPct'] }}%"></span></div>
                    <div class="d-flex justify-content-between small"><span class="fw-semibold">Paid</span><span class="tabular">{{ $kpis['orders']['paidPct'] }}%</span></div>
                    <div class="d-flex justify-content-between small text-muted-2"><span>Pending, cancelled or refunded</span><span class="tabular">{{ 100 - $kpis['orders']['paidPct'] }}%</span></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-body h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="kpi-title">New customers
                                @if ($kpis['customers']['change'] !== null)
                                    <span class="pill {{ $kpis['customers']['change'] >= 0 ? 'pill-success' : 'pill-warning' }} ms-1">{{ $kpis['customers']['change'] >= 0 ? '+' : '' }}{{ $kpis['customers']['change'] }}%</span>
                                @endif
                            </h3>
                            <div class="kpi-sub">Last 7 days</div>
                        </div>
                        <div class="kpi-value">{{ number_format($kpis['customers']['value']) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-body h-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="kpi-title">Average order</h3>
                            <div class="kpi-sub">Last 30 days</div>
                        </div>
                        <div class="kpi-value">{{ money($kpis['aov']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 g-xl-5">
            <div class="col-xl-8">
                <div class="d-flex justify-content-between align-items-end mb-3">
                    <div>
                        <h2 class="section-title">Recent orders</h2>
                        <p class="admin-subtitle">The latest orders across your store</p>
                    </div>
                    @can('orders.view')<a href="{{ route('admin.orders.index') }}" class="explore-link">View all <i class="bi bi-chevron-right small"></i></a>@endcan
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td>@can('orders.view')<a href="{{ route('admin.orders.show', $order) }}" class="table-link">#{{ $order->number }}</a>@else #{{ $order->number }} @endcan
                                        <div class="small text-muted-2">{{ $order->created_at->diffForHumans() }}</div></td>
                                    <td class="fw-semibold" style="color: var(--ms-heading)">{{ $order->shipping_name }}</td>
                                    <td>{{ $order->statusPill() }}</td>
                                    <td class="text-end fw-bold tabular" style="color: var(--ms-heading)">{{ money($order->total) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted-2 py-4">No orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xl-4 d-flex flex-column gap-4">
                <div class="panel panel-body">
                    <h3 class="kpi-title mb-1">Top sellers</h3>
                    <div class="kpi-sub mb-3">Last 30 days</div>
                    @forelse ($topProducts as $row)
                        <div class="d-flex justify-content-between gap-2 py-2 border-top small">
                            <span class="text-truncate fw-semibold" style="color: var(--ms-heading)">{{ $row->name }}</span>
                            <span class="nowrap text-muted-2 tabular">{{ $row->units }} sold</span>
                        </div>
                    @empty
                        <p class="small text-muted-2 mb-0">No sales yet.</p>
                    @endforelse
                </div>
                <div class="panel panel-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="kpi-title">Low stock</h3>
                        @can('products.view')<a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="small fw-bold">View all</a>@endcan
                    </div>
                    @forelse ($lowStock as $product)
                        <div class="d-flex justify-content-between align-items-center gap-2 py-2 border-top small">
                            <span class="text-truncate fw-semibold" style="color: var(--ms-heading)">{{ $product->name }}</span>
                            <span class="pill {{ $product->stock === 0 ? 'pill-danger' : 'pill-warning' }}">{{ $product->stock }}</span>
                        </div>
                    @empty
                        <p class="small text-muted-2 mb-0">Everything is well stocked.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</x-layouts.admin>
