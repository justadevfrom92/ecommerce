<x-layouts.admin :title="'Order '.$order->number">
    <x-slot:actions>
        <span class="badge fs-6 {{ $order->statusBadge() }}">{{ $order->statusLabel() }}</span>
    </x-slot:actions>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-body py-3"><h2 class="h6 mb-0">Items</h2></div>
                @include('orders._summary')
            </div>
            @if ($order->customer_note)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body"><h2 class="h6">Customer note</h2><p class="mb-0">{{ $order->customer_note }}</p></div>
                </div>
            @endif
        </div>
        <div class="col-xl-4">
            @can('orders.manage')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="h6 mb-3">Update order</h2>
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                            @csrf @method('PATCH')
                            <x-form.select name="status" label="Status" :value="$order->status" :options="collect(\App\Models\Order::STATUSES)->map->label->all()"
                                help="Cancelled or refunded puts the items back in stock. The customer is emailed when the status changes." />
                            <x-form.textarea name="admin_note" label="Internal note" :value="$order->admin_note" rows="3" help="Only visible to staff." />
                            <button class="btn btn-primary w-100">Save</button>
                        </form>
                    </div>
                </div>
            @endcan
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body small">
                    <h2 class="h6 mb-3">Customer</h2>
                    <p class="mb-1 fw-semibold">{{ $order->shipping_name }}</p>
                    <p class="mb-1"><a href="mailto:{{ $order->email }}">{{ $order->email }}</a></p>
                    @if ($order->user)
                        @can('users.manage')
                            <a href="{{ route('admin.users.edit', $order->user) }}">View account</a>
                        @endcan
                    @else
                        <span class="text-body-secondary">Account deleted</span>
                    @endif
                    <hr>
                    <h2 class="h6 mb-2">Ship to</h2>
                    <address class="mb-0">{!! collect($order->shippingAddressLines())->map(fn ($l) => e($l))->join('<br>') !!}</address>
                    <hr>
                    <h2 class="h6 mb-2">Payment</h2>
                    <dl class="row mb-0">
                        <dt class="col-5 fw-normal text-body-secondary">Placed</dt><dd class="col-7">{{ $order->created_at->format('M j, Y g:i A') }}</dd>
                        <dt class="col-5 fw-normal text-body-secondary">Paid</dt><dd class="col-7">{{ $order->paid_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                        @if ($order->stripe_payment_intent)
                            <dt class="col-5 fw-normal text-body-secondary">Stripe</dt><dd class="col-7 text-break"><a href="https://dashboard.stripe.com/payments/{{ $order->stripe_payment_intent }}" target="_blank" rel="noopener">{{ $order->stripe_payment_intent }}</a></dd>
                        @endif
                    </dl>
                </div>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="small"><i class="bi bi-arrow-left me-1"></i>All orders</a>
        </div>
    </div>
</x-layouts.admin>
