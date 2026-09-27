<x-layouts.store title="My orders">
    <div class="container-xxl py-4">
        @include('account._header')
        <x-data-table :rows="$orders" search-placeholder="Search order number" label="orders" empty="You haven't placed any orders yet." :columns="[
            'number' => ['label' => 'Order', 'sortable' => true],
            'status' => ['label' => 'Status', 'sortable' => true],
            'items' => ['label' => 'Items'],
            'created_at' => ['label' => 'Date', 'sortable' => true, 'class' => 'text-end'],
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
                    <td><a href="{{ route('account.orders.show', $order) }}" class="table-link">#{{ $order->number }}</a></td>
                    <td>{{ $order->statusPill() }}</td>
                    <td>{{ $order->items_count }}</td>
                    <td class="text-end text-muted-2 nowrap">{{ $order->created_at->format('M j, g:i A') }}</td>
                    <td class="text-end fw-bold tabular" style="color: var(--ms-heading)">{{ money($order->total) }}</td>
                    <td class="text-end"><a href="{{ route('account.orders.show', $order) }}" class="btn btn-soft btn-sm">View</a></td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
</x-layouts.store>
