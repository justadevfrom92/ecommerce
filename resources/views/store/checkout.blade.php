<x-layouts.store title="Check out">
    <div class="container-xxl py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Cart</a></li>
                <li class="breadcrumb-item active" aria-current="page">Check out</li>
            </ol>
        </nav>
        <h1 class="page-title mb-4">Check out</h1>

        @error('cart')
            <div class="alert alert-danger">{{ $message }} <a href="{{ route('cart.index') }}">Go to cart</a></div>
        @enderror

        <form method="POST" action="{{ route('checkout.store') }}" class="row g-4 g-xl-5">
            @csrf
            <div class="col-lg-7">
                <section class="pb-4 mb-4 border-bottom">
                    <h2 class="h4 fw-extrabold mb-1">Shipping Details</h2>
                    @php
                        $profile = ['shipping_name' => $user->name, 'shipping_phone' => $user->phone, 'shipping_line1' => $user->address_line1, 'shipping_line2' => $user->address_line2,
                            'shipping_city' => $user->city, 'shipping_state' => $user->state, 'shipping_postal_code' => $user->postal_code, 'shipping_country' => $user->country];
                        $f = fn ($field, $fallback = null) => old($field, ($user->hasAddress() ? $profile[$field] : null) ?? $last?->{$field} ?? $fallback);
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label" for="shipping_name">Full name</label>
                            <input class="form-control @error('shipping_name') is-invalid @enderror" id="shipping_name" name="shipping_name" value="{{ $f('shipping_name', $user->name) }}" required autocomplete="name">
                            @error('shipping_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="shipping_phone">Phone <span class="text-muted-2 fw-normal">(optional)</span></label>
                            <input class="form-control @error('shipping_phone') is-invalid @enderror" id="shipping_phone" name="shipping_phone" value="{{ $f('shipping_phone') }}" autocomplete="tel">
                            @error('shipping_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="shipping_line1">Address</label>
                            <input class="form-control @error('shipping_line1') is-invalid @enderror" id="shipping_line1" name="shipping_line1" value="{{ $f('shipping_line1') }}" required autocomplete="address-line1">
                            @error('shipping_line1')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="shipping_line2">Apartment, suite, etc. <span class="text-muted-2 fw-normal">(optional)</span></label>
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
                            <input class="form-control text-uppercase @error('shipping_country') is-invalid @enderror" id="shipping_country" name="shipping_country" value="{{ $f('shipping_country', 'US') }}" maxlength="2" required autocomplete="country">
                            @error('shipping_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </section>

                <section class="pb-4 mb-4 border-bottom">
                    <h2 class="h4 fw-extrabold mb-3">Order note</h2>
                    <label class="form-label" for="customer_note">Anything we should know? <span class="text-muted-2 fw-normal">(optional)</span></label>
                    <textarea class="form-control" id="customer_note" name="customer_note" rows="2">{{ old('customer_note') }}</textarea>
                </section>

                <section>
                    <h2 class="h4 fw-extrabold mb-3">Payment Method</h2>
                    @if ($stripeEnabled)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" checked disabled id="pay-card">
                            <label class="form-check-label fw-bold" for="pay-card" style="color: var(--ms-heading)">Credit or debit card <i class="bi bi-credit-card-2-front ms-1 text-muted-2"></i></label>
                        </div>
                    @else
                    @endif
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary px-5" style="min-width: 16rem">
                            @if ($stripeEnabled) Pay {{ money($totals['total']) }} @else Place order @endif
                        </button>
                        <a href="{{ route('cart.index') }}" class="btn btn-soft">Back to cart</a>
                    </div>
                </section>
            </div>

            <div class="col-lg-5 col-xl-4 offset-xl-1">
                <div class="panel panel-body summary">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="mb-0">Summary</h3>
                        <a href="{{ route('cart.index') }}" class="small fw-bold">Edit cart</a>
                    </div>
                    <ul class="list-unstyled mb-3 pb-2 dashed-bottom">
                        @foreach ($lines as $line)
                            <li class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ $line->product->imageUrl() }}" alt="" class="thumb" style="width: 2.5rem; height: 2.5rem">
                                <span class="small fw-semibold flex-grow-1" style="color: var(--ms-heading)">{{ $line->product->name }}</span>
                                <span class="small text-muted-2 nowrap">×{{ $line->quantity }}</span>
                                <span class="fw-bold tabular nowrap" style="color: var(--ms-heading)">{{ money($line->total) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    @include('store._totals', ['totals' => $totals])
                </div>
            </div>
        </form>
    </div>
</x-layouts.store>
