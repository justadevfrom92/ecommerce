<x-layouts.admin :title="'Order #'.$order->number" :crumbs="['Orders' => route('admin.orders.index')]">
    <x-slot:actions>
        {{ $order->statusPill() }}
        @if ($order->email)
            <a href="mailto:{{ $order->email }}" class="btn btn-soft btn-sm"><i class="bi bi-envelope me-1"></i>Email customer</a>
        @endif
    </x-slot:actions>

    <p class="text-muted-2 mt-n3 mb-4" style="color: var(--ms-heading)">Customer :
        @if ($order->user && auth()->user()->can('users.manage'))
            <a href="{{ route('admin.users.edit', $order->user) }}" class="fw-bold">{{ $order->user->name }}</a>
        @else
            <span class="fw-bold">{{ $order->shipping_name }}</span>
        @endif
    </p>

    <div class="row g-4 g-xl-5">
        <div class="col-xl-8">
            @include('orders._items')

            <div class="row g-4 mt-2">
                <div class="col-md-4">
                    <h2 class="h4 fw-extrabold mb-3">Customer details</h2>
                    <div class="detail-list">
                        <div class="detail-item"><i class="bi bi-person"></i><span class="k">Customer</span><span class="v">{{ $order->user?->name ?? 'Account deleted' }}</span></div>
                        <div class="detail-item"><i class="bi bi-envelope"></i><span class="k">Email</span><span class="v"><a href="mailto:{{ $order->email }}">{{ $order->email }}</a></span></div>
                        @if ($order->shipping_phone)
                            <div class="detail-item"><i class="bi bi-telephone"></i><span class="k">Phone</span><span class="v">{{ $order->shipping_phone }}</span></div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <h2 class="h4 fw-extrabold mb-3">Shipping details</h2>
                    <div class="detail-list">
                        <div class="detail-item"><i class="bi bi-person-badge"></i><span class="k">Recipient</span><span class="v">{{ $order->shipping_name }}</span></div>
                        <div class="detail-item"><i class="bi bi-house"></i><span class="k">Address</span><span class="v">{{ $order->shipping_line1 }}@if ($order->shipping_line2), {{ $order->shipping_line2 }}@endif<br>{{ trim($order->shipping_city.', '.$order->shipping_state, ', ') }} {{ $order->shipping_postal_code }}<br>{{ $order->shipping_country }}</span></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h2 class="h4 fw-extrabold mb-3">Other details</h2>
                    <div class="detail-list">
                        <div class="detail-item"><i class="bi bi-calendar"></i><span class="k">Placed</span><span class="v">{{ $order->created_at->format('M j, Y g:i A') }}</span></div>
                        <div class="detail-item"><i class="bi bi-credit-card"></i><span class="k">Paid</span><span class="v">{{ $order->paid_at?->format('M j, Y g:i A') ?? 'Not yet' }}</span></div>
                        @if ($order->stripe_payment_intent)
                            <div class="detail-item"><i class="bi bi-receipt"></i><span class="k">Stripe payment</span><span class="v text-break"><a href="https://dashboard.stripe.com/payments/{{ $order->stripe_payment_intent }}" target="_blank" rel="noopener">{{ $order->stripe_payment_intent }}</a></span></div>
                        @endif
                        @if ($order->customer_note)
                            <div class="detail-item"><i class="bi bi-chat-left-text"></i><span class="k">Customer note</span><span class="v">{{ $order->customer_note }}</span></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            @include('orders._summary')
            @can('orders.manage')
                <div class="panel panel-body summary">
                    <h3 class="mb-3">Update order</h3>
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                        @csrf @method('PATCH')
                        <x-form.select name="status" label="Order status" :value="$order->status" :options="collect(\App\Models\Order::STATUSES)->map->label->all()"
                            help="Cancelled or refunded puts the items back in stock. The customer is emailed when it ships, arrives, or is cancelled/refunded." />
                        <x-form.textarea name="admin_note" label="Internal note" :value="$order->admin_note" rows="3" help="Only visible to staff." />
                        <button class="btn btn-primary w-100">Save</button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</x-layouts.admin>
