<dl class="row small mb-0">
    <dt class="col-6 fw-normal">Subtotal</dt><dd class="col-6 text-end">{{ money($totals['subtotal']) }}</dd>
    <dt class="col-6 fw-normal">Shipping</dt><dd class="col-6 text-end">{{ $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' }}</dd>
    @if ($totals['tax'] > 0)
        <dt class="col-6 fw-normal">Tax</dt><dd class="col-6 text-end">{{ money($totals['tax']) }}</dd>
    @endif
    <dt class="col-6 border-top pt-2 fs-6">Total</dt><dd class="col-6 text-end border-top pt-2 fs-6 fw-bold">{{ money($totals['total']) }}</dd>
</dl>
@if ($totals['shipping'] > 0 && (float) setting('free_shipping_over') > 0)
    <p class="small text-body-secondary mt-2 mb-0">Add {{ money((float) setting('free_shipping_over') - $totals['subtotal']) }} more for free shipping.</p>
@endif
