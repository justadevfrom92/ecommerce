<x-layouts.store :title="'Order '.$order->number">
    <div class="container-xxl py-4">
        <h1 class="h3 mb-4">My account</h1>
        <div class="row g-4">
            <div class="col-lg-3">@include('account._nav')</div>
            <div class="col-lg-9">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <h2 class="h5 mb-1">Order {{ $order->number }}</h2>
                                <div class="small text-body-secondary">Placed {{ $order->created_at->format('M j, Y g:i A') }}</div>
                            </div>
                            <span class="badge fs-6 {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span>
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

                        <div class="row g-4 mb-2">
                            <div class="col-sm-6">
                                <h3 class="h6 text-uppercase small text-body-secondary">Ship to</h3>
                                <address class="mb-0 small">{!! collect($order->shippingAddressLines())->map(fn ($l) => e($l))->join('<br>') !!}</address>
                            </div>
                            @if ($order->customer_note)
                                <div class="col-sm-6">
                                    <h3 class="h6 text-uppercase small text-body-secondary">Your note</h3>
                                    <p class="small mb-0">{{ $order->customer_note }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    @include('orders._summary')
                </div>
                <a href="{{ route('account.orders.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i>All orders</a>
            </div>
        </div>
    </div>
</x-layouts.store>
