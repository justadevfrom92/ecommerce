<x-layouts.store title="Your cart">
    <div class="container-xxl py-3">
        <h1 class="h3 mb-4">Your cart</h1>

        @if ($lines->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-cart3 fs-1 text-body-secondary d-block mb-3"></i>
                <p class="mb-3">Your cart is empty.</p>
                <a href="{{ route('search') }}" class="btn btn-primary">Start shopping</a>
            </div>
        @else
            <div class="row g-4 mb-5">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <ul class="list-group list-group-flush">
                            @foreach ($lines as $line)
                                <li class="list-group-item py-3">
                                    <div class="d-flex gap-3 align-items-center">
                                        <a href="{{ route('products.show', $line->product) }}" class="flex-shrink-0">
                                            <img src="{{ $line->product->imageUrl() }}" alt="" width="72" height="72" class="rounded object-fit-cover bg-body-tertiary">
                                        </a>
                                        <div class="flex-grow-1 min-w-0">
                                            <a href="{{ route('products.show', $line->product) }}" class="fw-semibold text-reset text-decoration-none d-block text-truncate">{{ $line->product->name }}</a>
                                            <div class="small text-body-secondary">{{ money($line->product->price) }} each</div>
                                        </div>
                                        <form method="POST" action="{{ route('cart.update', $line->product) }}" class="flex-shrink-0">
                                            @csrf @method('PATCH')
                                            <label for="qty-{{ $line->product->id }}" class="visually-hidden">Quantity for {{ $line->product->name }}</label>
                                            <select id="qty-{{ $line->product->id }}" name="quantity" class="form-select form-select-sm" data-auto-submit>
                                                @for ($i = 0; $i <= min(99, max($line->product->stock, $line->quantity)); $i++)
                                                    <option value="{{ $i }}" @selected($i === $line->quantity)>{{ $i === 0 ? 'Remove' : $i }}</option>
                                                @endfor
                                            </select>
                                            <noscript><button class="btn btn-sm btn-link">Update</button></noscript>
                                        </form>
                                        <div class="fw-semibold text-end flex-shrink-0" style="min-width: 5rem">{{ money($line->total) }}</div>
                                        <form method="POST" action="{{ route('cart.destroy', $line->product) }}" class="flex-shrink-0">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-link text-body-secondary" aria-label="Remove {{ $line->product->name }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h2 class="h5 mb-3">Order summary</h2>
                            @include('store._totals', ['totals' => $totals])
                            @if (Route::has('checkout.create'))
                                <a href="{{ route('checkout.create') }}" class="btn btn-primary w-100 mt-3">Proceed to checkout</a>
                            @endif
                            <a href="{{ route('search') }}" class="btn btn-link w-100 mt-1">Continue shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <x-product-slider title="Customers also bought" :products="$suggestions" />
    </div>
</x-layouts.store>
