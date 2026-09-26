{{-- Order line items + totals, shared by the customer and admin order pages. --}}
<div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="small text-uppercase text-body-secondary">Item</th>
                <th class="small text-uppercase text-body-secondary text-end">Price</th>
                <th class="small text-uppercase text-body-secondary text-end">Qty</th>
                <th class="small text-uppercase text-body-secondary text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        <div class="fw-semibold">
                            @if ($item->product && $item->product->is_active)
                                <a href="{{ route('products.show', $item->product) }}" class="text-reset">{{ $item->name }}</a>
                            @else
                                {{ $item->name }}
                            @endif
                        </div>
                        <div class="small text-body-secondary">SKU {{ $item->sku }}</div>
                    </td>
                    <td class="text-end">{{ money($item->price) }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">{{ money($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="small">
            <tr><td colspan="3" class="text-end border-0">Subtotal</td><td class="text-end border-0">{{ money($order->subtotal) }}</td></tr>
            <tr><td colspan="3" class="text-end border-0">Shipping</td><td class="text-end border-0">{{ (float) $order->shipping > 0 ? money($order->shipping) : 'Free' }}</td></tr>
            @if ((float) $order->tax > 0)
                <tr><td colspan="3" class="text-end border-0">Tax</td><td class="text-end border-0">{{ money($order->tax) }}</td></tr>
            @endif
            <tr class="fs-6"><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold">{{ money($order->total) }}</td></tr>
        </tfoot>
    </table>
</div>
