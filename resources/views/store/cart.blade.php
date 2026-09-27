<x-layouts.store title="Cart">
    <div class="container-xxl py-4">
        <h1 class="page-title mb-4">Cart</h1>

        @if ($lines->isEmpty())
            <div class="panel panel-body text-center py-5 mb-5">
                <i class="bi bi-cart3 fs-1 text-muted-2 d-block mb-3"></i>
                <p class="fw-bold mb-3" style="color: var(--ms-heading)">Your cart is empty.</p>
                <a href="{{ route('search') }}" class="btn btn-primary">Start shopping</a>
            </div>
        @else
            <div class="row g-4 g-xl-5 mb-5">
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 4rem"></th>
                                    <th scope="col">Products</th>
                                    <th scope="col" class="text-end">Price</th>
                                    <th scope="col" class="text-center">Quantity</th>
                                    <th scope="col" class="text-end">Total</th>
                                    <th scope="col"><span class="visually-hidden">Remove</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lines as $line)
                                    <tr>
                                        <td><a href="{{ route('products.show', $line->product) }}"><img src="{{ $line->product->imageUrl() }}" alt="" class="thumb"></a></td>
                                        <td style="min-width: 14rem"><a href="{{ route('products.show', $line->product) }}" class="table-link">{{ $line->product->name }}</a></td>
                                        <td class="text-end tabular">{{ money($line->product->price) }}</td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('cart.update', $line->product) }}">
                                                @csrf @method('PATCH')
                                                <div class="qty-stepper" data-qty>
                                                    <button type="button" class="btn btn-link text-body p-0" data-qty-step="-1" aria-label="Decrease {{ $line->product->name }}"><i class="bi bi-dash"></i></button>
                                                    <label for="qty-{{ $line->product->id }}" class="visually-hidden">Quantity for {{ $line->product->name }}</label>
                                                    <input type="number" id="qty-{{ $line->product->id }}" name="quantity" value="{{ $line->quantity }}" min="0" max="{{ min(99, max($line->product->stock, $line->quantity)) }}" data-auto-submit>
                                                    <button type="button" class="btn btn-link text-body p-0" data-qty-step="1" aria-label="Increase {{ $line->product->name }}"><i class="bi bi-plus"></i></button>
                                                </div>
                                                <noscript><button class="btn btn-sm btn-link">Update</button></noscript>
                                            </form>
                                        </td>
                                        <td class="text-end fw-bold tabular" style="color: var(--ms-heading)">{{ money($line->total) }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('cart.destroy', $line->product) }}">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-link text-muted-2 p-1" aria-label="Remove {{ $line->product->name }}"><i class="bi bi-trash-fill"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td colspan="4" class="fw-bold fs-6" style="color: var(--ms-heading)">Items subtotal :</td>
                                    <td class="text-end fw-extrabold fs-6 tabular" style="color: var(--ms-heading)">{{ money($totals['subtotal']) }}</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="panel panel-body summary">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0">Summary</h3>
                            <a href="{{ route('search') }}" class="small fw-bold">Keep shopping</a>
                        </div>
                        @include('store._totals', ['totals' => $totals])
                        <a href="{{ route('checkout.create') }}" class="btn btn-primary w-100">Proceed to check out <i class="bi bi-chevron-right small"></i></a>
                    </div>
                </div>
            </div>
        @endif

        <x-product-slider title="Customers also bought" :products="$suggestions" :view-all="route('search')" />
    </div>
</x-layouts.store>
