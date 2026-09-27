<x-layouts.store :title="'Order '.$order->number">
    <div class="container-xxl py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <h1 class="page-title">Order #{{ $order->number }}</h1>
                <p class="text-muted-2 mb-0 mt-1">Placed {{ $order->created_at->format('M j, Y g:i A') }}</p>
            </div>
            {{ $order->statusPill() }}
        </div>

        @if ($order->isAwaitingPayment() && app(\App\Services\Stripe::class)->enabled())
            <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>This order hasn't been paid yet.</span>
                <form method="POST" action="{{ route('checkout.pay', $order) }}">
                    @csrf
                    <button class="btn btn-primary btn-sm"><i class="bi bi-credit-card me-1"></i>Pay now</button>
                </form>
            </div>
        @endif

        <div class="row g-4 g-xl-5">
            <div class="col-lg-8">
                @include('orders._items')
                <div class="row g-4 mt-2">
                    <div class="col-sm-6">
                        <h2 class="h4 fw-extrabold mb-3">Shipping details</h2>
                        <div class="detail-list">
                            <div class="detail-item"><i class="bi bi-person"></i><span class="k">Name</span><span class="v">{{ $order->shipping_name }}</span></div>
                            <div class="detail-item"><i class="bi bi-house"></i><span class="k">Address</span><span class="v">{{ $order->shipping_line1 }}@if ($order->shipping_line2), {{ $order->shipping_line2 }}@endif<br>{{ trim($order->shipping_city.', '.$order->shipping_state, ', ') }} {{ $order->shipping_postal_code }}<br>{{ $order->shipping_country }}</span></div>
                            @if ($order->shipping_phone)
                                <div class="detail-item"><i class="bi bi-telephone"></i><span class="k">Phone</span><span class="v">{{ $order->shipping_phone }}</span></div>
                            @endif
                        </div>
                    </div>
                    @if ($order->customer_note)
                        <div class="col-sm-6">
                            <h2 class="h4 fw-extrabold mb-3">Your note</h2>
                            <p class="text-muted-2">{{ $order->customer_note }}</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4">
                @include('orders._summary')
            </div>
        </div>
    </div>
</x-layouts.store>
