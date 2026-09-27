<x-layouts.store :title="$product->name">
    <div class="container-xxl py-4">

        <div class="row g-4 g-lg-5 mb-5 mt-1">
            <div class="col-lg-6">
                <div class="pd-gallery mb-3">
                    <div class="pd-thumbs d-flex flex-column gap-2">
                        <span class="pd-thumb active" style="--tint: {{ $product->tint() }}"><img src="{{ $product->imageUrl() }}" alt=""></span>
                    </div>
                    <div class="pd-main" style="--tint: {{ $product->tint() }}"><img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"></div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    @if ($product->is_featured && $product->inStock())<span class="pill pill-success">Featured <i class="bi bi-check-lg"></i></span>@endif
                    <a href="{{ route('categories.show', $product->category) }}" class="small fw-bold">{{ $product->category->name }}</a>
                </div>
                <h1 class="pd-title mb-3">{{ $product->name }}</h1>

                <div class="d-flex flex-wrap align-items-baseline gap-3 mb-2">
                    <span class="pd-price tabular">{{ money($product->price) }}</span>
                    @if ($product->isOnSale())
                        <span class="price-old fs-5">{{ money($product->compare_at_price) }}</span>
                        <span class="pd-off">{{ round((1 - $product->price / $product->compare_at_price) * 100) }}% off</span>
                    @endif
                </div>

                @if ($product->inStock())
                    <p class="fw-bold mb-3" style="color: var(--ms-green)">In stock @if ($product->stock <= 5)<span class="text-danger">— only {{ $product->stock }} left</span>@endif</p>
                @else
                    <p class="fw-bold text-danger mb-3">Out of stock</p>
                @endif

                <p class="mb-4" style="color: var(--ms-heading)">
                    @if ((float) setting('free_shipping_over') > 0)
                        <strong>Free shipping</strong> on orders over {{ money(setting('free_shipping_over')) }}.
                    @endif
                    SKU <span class="text-muted-2">{{ $product->sku }}</span>
                </p>

                @if ($product->inStock())
                    <form method="POST" action="{{ route('cart.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="mb-4">
                            <label for="quantity" class="form-label d-block">Quantity</label>
                            <div class="qty-stepper" data-qty>
                                <button type="button" class="btn btn-soft" data-qty-step="-1" aria-label="Decrease quantity"><i class="bi bi-dash"></i></button>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ min(99, $product->stock) }}">
                                <button type="button" class="btn btn-soft" data-qty-step="1" aria-label="Increase quantity"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                        <div class="row g-3" style="max-width: 32rem">
                            <div class="col-sm-6">
                                <button type="submit" class="btn btn-lg btn-outline-primary w-100 rounded-pill"><i class="bi bi-cart-plus me-2"></i>Add to cart</button>
                            </div>
                            <div class="col-sm-6">
                                <button type="submit" formaction="{{ route('cart.buy-now') }}" class="btn btn-lg btn-primary w-100 rounded-pill">Quick pay</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <div class="row g-4 g-lg-5 mb-5">
            <div class="col-lg-7">
                <ul class="nav tabs-underline mb-3" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-desc" type="button" role="tab" aria-controls="tab-desc" aria-selected="true">Description</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-spec" type="button" role="tab" aria-controls="tab-spec" aria-selected="false">Specification</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-desc" role="tabpanel">
                        <div style="color: var(--ms-heading); max-width: 65ch">{!! nl2br(e($product->description ?: 'No description yet.')) !!}</div>
                    </div>
                    <div class="tab-pane fade" id="tab-spec" role="tabpanel">
                        <table class="table">
                            <tbody>
                                <tr><th scope="row" class="fw-bold" style="width: 12rem">SKU</th><td>{{ $product->sku }}</td></tr>
                                <tr><th scope="row" class="fw-bold">Department</th><td>{{ $product->category->department->name }}</td></tr>
                                <tr><th scope="row" class="fw-bold">Category</th><td>{{ $product->category->name }}</td></tr>
                                <tr><th scope="row" class="fw-bold">Availability</th><td>{{ $product->inStock() ? $product->stock.' in stock' : 'Out of stock' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="panel panel-body">
                    <h2 class="h6 fw-extrabold mb-3">Delivery &amp; returns</h2>
                    <div class="detail-list">
                        <div class="detail-item"><i class="bi bi-truck"></i><span class="k">Shipping</span><span class="v">{{ (float) setting('shipping_flat_rate') > 0 ? money(setting('shipping_flat_rate')).' flat rate' : 'Free' }}@if ((float) setting('free_shipping_over') > 0), free over {{ money(setting('free_shipping_over')) }}@endif</span></div>
                        <div class="detail-item"><i class="bi bi-shield-check"></i><span class="k">Secure checkout</span><span class="v">Pay safely by card on Stripe.</span></div>
                    </div>
                </div>
            </div>
        </div>

        <x-product-slider title="Similar Products" subtitle="Essential for a better life" :products="$related" :view-all="route('categories.show', $product->category)" />
        <x-product-slider :title="'More from '.$product->category->department->name" :products="$departmentPicks" :view-all="route('departments.show', $product->category->department)" />
    </div>
</x-layouts.store>
