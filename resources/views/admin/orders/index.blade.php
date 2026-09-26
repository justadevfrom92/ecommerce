<x-layouts.admin title="Orders">
    <x-data-table :rows="$orders" search-placeholder="Order #, name or email…" label="orders" :columns="[
        'number' => ['label' => 'Order', 'sortable' => true],
        'shipping_name' => ['label' => 'Customer', 'sortable' => true],
        'created_at' => ['label' => 'Date', 'sortable' => true],
        'items' => ['label' => 'Items', 'class' => 'text-end'],
        'total' => ['label' => 'Total', 'sortable' => true, 'class' => 'text-end'],
        'status' => ['label' => 'Status', 'sortable' => true],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:filters>
            <select name="status" class="form-select form-select-sm w-auto" aria-label="Filter by status" data-auto-submit>
                <option value="">Any status</option>
                @foreach (\App\Models\Order::STATUSES as $value => $status)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $status['label'] }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm w-auto" aria-label="From date">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm w-auto" aria-label="To date">
        </x-slot:filters>
        @foreach ($orders as $order)
            <tr>
                <td class="fw-semibold"><a href="{{ route('admin.orders.show', $order) }}" class="text-reset">{{ $order->number }}</a></td>
                <td><div>{{ $order->shipping_name }}</div><div class="small text-body-secondary">{{ $order->email }}</div></td>
                <td class="small text-body-secondary text-nowrap">{{ $order->created_at->format('M j, Y g:i A') }}</td>
                <td class="text-end">{{ $order->items_count }}</td>
                <td class="text-end text-nowrap">{{ money($order->total) }}</td>
                <td><span class="badge {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span></td>
                <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
