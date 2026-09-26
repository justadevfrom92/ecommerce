<x-layouts.store title="My orders">
    <div class="container-xxl py-4">
        <h1 class="h3 mb-4">My account</h1>
        <div class="row g-4">
            <div class="col-lg-3">@include('account._nav')</div>
            <div class="col-lg-9">
                <x-data-table :rows="$orders" search-placeholder="Search order number…" label="orders" empty="You haven't placed any orders yet." :columns="[
                    'number' => ['label' => 'Order', 'sortable' => true],
                    'created_at' => ['label' => 'Date', 'sortable' => true],
                    'items' => ['label' => 'Items'],
                    'status' => ['label' => 'Status', 'sortable' => true],
                    'total' => ['label' => 'Total', 'sortable' => true, 'class' => 'text-end'],
                    'actions' => ['label' => '', 'class' => 'text-end'],
                ]">
                    <x-slot:filters>
                        <select name="status" class="form-select form-select-sm w-auto" aria-label="Filter by status" data-auto-submit>
                            <option value="">Any status</option>
                            @foreach (\App\Models\Order::STATUSES as $value => $status)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $status['label'] }}</option>
                            @endforeach
                        </select>
                    </x-slot:filters>
                    @foreach ($orders as $order)
                        <tr>
                            <td class="fw-semibold">{{ $order->number }}</td>
                            <td class="small text-body-secondary">{{ $order->created_at->format('M j, Y') }}</td>
                            <td>{{ $order->items_count }}</td>
                            <td><span class="badge {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span></td>
                            <td class="text-end">{{ money($order->total) }}</td>
                            <td class="text-end"><a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @endforeach
                </x-data-table>
            </div>
        </div>
    </div>
</x-layouts.store>
