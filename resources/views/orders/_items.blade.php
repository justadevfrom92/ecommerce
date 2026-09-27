{{-- Order line items table (customer + admin order pages). --}}
<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th scope="col" style="width: 4rem"></th>
                <th scope="col">Products</th>
                <th scope="col" class="text-end">Price</th>
                <th scope="col" class="text-end">Qty</th>
                <th scope="col" class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td><img src="{{ $item->product?->imageUrl() ?? asset('images/placeholder.svg') }}" alt="" class="thumb"></td>
                    <td style="min-width: 14rem">
                        @if ($item->product && $item->product->is_active)
                            <a href="{{ route('products.show', $item->product) }}" class="table-link">{{ $item->name }}</a>
                        @else
                            <span class="fw-semibold">{{ $item->name }}</span>
                        @endif
                        <div class="small text-muted-2">SKU {{ $item->sku }}</div>
                    </td>
                    <td class="text-end tabular">{{ money($item->price) }}</td>
                    <td class="text-end tabular">{{ $item->quantity }}</td>
                    <td class="text-end fw-bold tabular" style="color: var(--ms-heading)">{{ money($item->line_total) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" class="fw-bold fs-6" style="color: var(--ms-heading)">Items subtotal :</td>
                <td class="text-end fw-extrabold fs-6 tabular" style="color: var(--ms-heading)">{{ money($order->subtotal) }}</td>
            </tr>
        </tbody>
    </table>
</div>
