{{-- Summary rows + total. Expects $totals. --}}
<div class="summary-row"><span class="label">Items subtotal :</span><span class="value">{{ money($totals['subtotal']) }}</span></div>
@if ($totals['tax'] > 0)
    <div class="summary-row"><span class="label">Tax :</span><span class="value">{{ money($totals['tax']) }}</span></div>
@endif
<div class="summary-row"><span class="label">Shipping cost :</span><span class="value">{{ $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' }}</span></div>
<div class="summary-total"><span>Total :</span><span>{{ money($totals['total']) }}</span></div>
