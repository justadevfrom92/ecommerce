<x-layouts.admin title="Orders">
    @php($active = request('status'))
    <x-data-table :rows="$orders" search-placeholder="Search orders" label="orders" :keep="['status']" :columns="[
        'number' => ['label' => 'Order', 'sortable' => true],
        'total' => ['label' => 'Total', 'sortable' => true, 'class' => 'text-end'],
        'shipping_name' => ['label' => 'Customer', 'sortable' => true],
        'status' => ['label' => 'Status', 'sortable' => true],
        'items' => ['label' => 'Items', 'class' => 'text-end'],
        'created_at' => ['label' => 'Date', 'sortable' => true, 'class' => 'text-end'],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:tabs>
            <a href="{{ route('admin.orders.index') }}" @class(['active' => ! $active])>All <span class="count">({{ number_format($counts->sum()) }})</span></a>
            @foreach (\App\Models\Order::STATUSES as $value => $status)
                <a href="{{ route('admin.orders.index', ['status' => $value]) }}" @class(['active' => $active === $value])>{{ $status['label'] }} <span class="count">({{ number_format($counts[$value] ?? 0) }})</span></a>
            @endforeach
        </x-slot:tabs>
        <x-slot:filters>
            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm w-auto" aria-label="From date">
            <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm w-auto" aria-label="To date">
        </x-slot:filters>
        @foreach ($orders as $order)
            <tr>
                <td><a href="{{ route('admin.orders.show', $order) }}" class="table-link">#{{ $order->number }}</a></td>
                <td class="text-end fw-bold tabular nowrap" style="color: var(--ms-heading)">{{ money($order->total) }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar-sm">{{ mb_substr($order->shipping_name, 0, 1) }}</span>
                        <div class="min-w-0">
                            <div class="fw-bold" style="color: var(--ms-heading)">{{ $order->shipping_name }}</div>
                            <div class="small text-muted-2 text-truncate">{{ $order->email }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $order->statusPill() }}</td>
                <td class="text-end tabular">{{ $order->items_count }}</td>
                <td class="text-end text-muted-2 nowrap">{{ $order->created_at->format('M j, g:i A') }}</td>
                <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-soft btn-sm">View</a></td>
            </tr>
        @endforeach
    </x-data-table>
</x-layouts.admin>
