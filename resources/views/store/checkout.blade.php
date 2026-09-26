<x-layouts.store title="Checkout">
    <div class="container-xxl py-3">
        <h1 class="h3 mb-4">Checkout</h1>

        @error('cart')
            <div class="alert alert-danger">{{ $message }} <a href="{{ route('cart.index') }}">Go to cart</a></div>
        @enderror

        <form method="POST" action="{{ route('checkout.store') }}" class="row g-4">
            @csrf
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Shipping address</h2>
                        <p class="small text-body-secondary">Order updates go to <strong>{{ $user->email }}</strong>.</p>
                        @php($f = fn ($field, $fallback = null) => old($field, $last?->{$field} ?? $fallback))
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label" for="shipping_name">Full name</label>
                                <input class="form-control @error('shipping_name') is-invalid @enderror" id="shipping_name" name="shipping_name" value="{{ $f('shipping_name', $user->name) }}" required autocomplete="name">
                                @error('shipping_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="shipping_phone">Phone <span class="text-body-secondary small">(optional)</span></label>
                                <input class="form-control @error('shipping_phone') is-invalid @enderror" id="shipping_phone" name="shipping_phone" value="{{ $f('shipping_phone') }}" autocomplete="tel">
                                @error('shipping_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="shipping_line1">Address</label>
                                <input class="form-control @error('shipping_line1') is-invalid @enderror" id="shipping_line1" name="shipping_line1" value="{{ $f('shipping_line1') }}" required autocomplete="address-line1">
                                @error('shipping_line1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="shipping_line2">Apartment, suite, etc. <span class="text-body-secondary small">(optional)</span></label>
                                <input class="form-control" id="shipping_line2" name="shipping_line2" value="{{ $f('shipping_line2') }}" autocomplete="address-line2">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="shipping_city">City</label>
                                <input class="form-control @error('shipping_city') is-invalid @enderror" id="shipping_city" name="shipping_city" value="{{ $f('shipping_city') }}" required autocomplete="address-level2">
                                @error('shipping_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="shipping_state">State / region</label>
                                <input class="form-control" id="shipping_state" name="shipping_state" value="{{ $f('shipping_state') }}" autocomplete="address-level1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="shipping_postal_code">Postal code</label>
                                <input class="form-control @error('shipping_postal_code') is-invalid @enderror" id="shipping_postal_code" name="shipping_postal_code" value="{{ $f('shipping_postal_code') }}" required autocomplete="postal-code">
                                @error('shipping_postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="shipping_country">Country code</label>
                                <input class="form-control text-uppercase @error('shipping_country') is-invalid @enderror" id="shipping_country" name="shipping_country" value="{{ $f('shipping_country', 'US') }}" maxlength="2" required autocomplete="country" aria-describedby="country-help">
                                <div id="country-help" class="form-text">Two letters, e.g. US, CA, GB.</div>
                                @error('shipping_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="customer_note">Order note <span class="text-body-secondary small">(optional)</span></label>
                                <textarea class="form-control" id="customer_note" name="customer_note" rows="2">{{ old('customer_note') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Your order</h2>
                        <ul class="list-unstyled small mb-3">
                            @foreach ($lines as $line)
                                <li class="d-flex justify-content-between gap-2 mb-2">
                                    <span class="text-truncate">{{ $line->quantity }} × {{ $line->product->name }}</span>
                                    <span class="text-nowrap">{{ money($line->total) }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @include('store._totals', ['totals' => $totals])
                        <button type="submit" class="btn btn-primary w-100 mt-4">
                            @if ($stripeEnabled)<i class="bi bi-lock me-1"></i>Continue to secure payment @else Place order @endif
                        </button>
                        @if ($stripeEnabled)
                            <p class="small text-body-secondary text-center mt-2 mb-0">You'll pay on Stripe's secure checkout page.</p>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layouts.store>
