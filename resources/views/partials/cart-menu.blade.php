{{-- Cart icon with a popup listing the cart; footer shows the total and "View cart". --}}
<div class="dropdown">
    <button class="nav-icon-btn position-relative" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Cart, {{ $cartCount }} items">
        <i class="bi bi-cart3"></i>
        @if ($cartCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill cart-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-sm p-0 cart-menu">
        <div class="d-flex justify-content-between align-items-center px-3 py-3 border-bottom">
            <h6 class="fw-extrabold mb-0">Your cart</h6>
            <span class="small text-muted-2">{{ $cartCount }} {{ \Illuminate\Support\Str::plural('item', $cartCount) }}</span>
        </div>
        @if ($cartLines->isEmpty())
            <div class="text-center px-3 py-4">
                <i class="bi bi-cart3 fs-3 text-muted-2 d-block mb-2"></i>
                <p class="small fw-semibold mb-3" style="color: var(--ms-heading)">Your cart is empty.</p>
                <a href="{{ route('search') }}" class="btn btn-soft btn-sm">Start shopping</a>
            </div>
        @else
            <ul class="list-unstyled mb-0 cart-menu-items">
                @foreach ($cartLines as $line)
                    <li class="d-flex align-items-center gap-3 px-3 py-2">
                        <a href="{{ route('products.show', $line->product) }}" class="flex-shrink-0"><img src="{{ $line->product->imageUrl() }}" alt="" class="thumb" style="width: 3rem; height: 3rem"></a>
                        <div class="flex-grow-1 min-w-0">
                            <a href="{{ route('products.show', $line->product) }}" class="d-block small fw-bold text-truncate" style="color: var(--ms-heading)">{{ $line->product->name }}</a>
                            <span class="small text-muted-2">{{ $line->quantity }} × {{ money($line->product->price) }}</span>
                        </div>
                        <span class="small fw-bold tabular nowrap" style="color: var(--ms-heading)">{{ money($line->total) }}</span>
                        <form method="POST" action="{{ route('cart.destroy', $line->product) }}">
                            @csrf @method('DELETE')
                            <button class="btn btn-link text-muted-2 p-1" aria-label="Remove {{ $line->product->name }}"><i class="bi bi-x-lg small"></i></button>
                        </form>
                    </li>
                @endforeach
            </ul>
            <div class="border-top p-3">
                <div class="d-flex justify-content-between small mb-1"><span class="fw-semibold">Subtotal</span><span class="tabular">{{ money($cartTotals['subtotal']) }}</span></div>
                <div class="d-flex justify-content-between small mb-3"><span class="fw-semibold">Shipping</span><span class="tabular">{{ $cartTotals['shipping'] > 0 ? money($cartTotals['shipping']) : 'Free' }}</span></div>
                <a href="{{ route('cart.index') }}" class="btn btn-primary w-100 d-flex justify-content-between align-items-center">
                    <span>View cart</span><span class="tabular">{{ money($cartTotals['total']) }}</span>
                </a>
                <a href="{{ route('checkout.create') }}" class="btn btn-link btn-sm w-100 mt-1 fw-bold">Check out</a>
            </div>
        @endif
    </div>
</div>
