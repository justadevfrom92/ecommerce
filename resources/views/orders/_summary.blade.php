{{-- Order totals card. --}}
<div class="panel panel-body summary mb-3">
    <h3 class="mb-3">Summary</h3>
    <div class="summary-row"><span class="label">Items subtotal :</span><span class="value">{{ money($order->subtotal) }}</span></div>
    @if ((float) $order->tax > 0)
        <div class="summary-row"><span class="label">Tax :</span><span class="value">{{ money($order->tax) }}</span></div>
    @endif
    <div class="summary-row"><span class="label">Shipping cost :</span><span class="value">{{ (float) $order->shipping > 0 ? money($order->shipping) : 'Free' }}</span></div>
    <div class="summary-total mb-0"><span>Total :</span><span>{{ money($order->total) }}</span></div>
</div>
