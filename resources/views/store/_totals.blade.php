{{-- Summary rows + total. Expects $totals. --}}
<div class="summary-row"><span class="label">Items subtotal :</span><span class="value">{{ money($totals['subtotal']) }}</span></div>
@if ($totals['tax'] > 0)
    <div class="summary-row"><span class="label">Tax :</span><span class="value">{{ money($totals['tax']) }}</span></div>
@endif
<div class="summary-row"><span class="label">Shipping cost :</span><span class="value">{{ $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' }}</span></div>
<div class="summary-total"><span>Total :</span><span>{{ money($totals['total']) }}</span></div>
@if ($totals['shipping'] > 0 && (float) setting('free_shipping_over') > 0)
    <p class="small text-muted-2 mt-n2 mb-3">Add {{ money((float) setting('free_shipping_over') - $totals['subtotal']) }} more for free shipping.</p>
@endif
