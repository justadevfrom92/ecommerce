<x-layouts.store>
    <div class="container-xxl pt-4">
        {{-- Promo banners --}}
        <section class="promo promo-hero mb-3" aria-label="Sale">
            <div class="promo-body">
                <h2>
                    @if ($maxDiscount > 0) <span class="hl">Up to {{ $maxDiscount }}% off</span> @else <span class="hl">New arrivals</span> @endif
                </h2>
                <p>on everyday items</p>
                <a href="{{ route('search', ['on_sale' => 1]) }}" class="btn btn-primary">Shop now</a>
            </div>
            <i class="bi bi-speaker promo-art" aria-hidden="true"></i>
        </section>
        <div class="row g-3 mb-5">
            <div class="col-md-6">
                <section class="promo promo-gift h-100" aria-label="Free shipping">
                    <div class="promo-body">
                        <h2>Get <span class="hl">free shipping</span></h2>
                        <p>on orders over {{ money(setting('free_shipping_over')) }}</p>
                        <a href="{{ route('search') }}" class="btn btn-primary">Buy now</a>
                    </div>
                    <i class="bi bi-gift promo-art" aria-hidden="true"></i>
                </section>
            </div>
            @if ($spotlight)
                <div class="col-md-6">
                    <section class="promo promo-blue h-100" aria-label="{{ $spotlight->name }}">
                        <div class="promo-body">
                            <h2>{{ $spotlight->name }}</h2>
                            <p>Best in the market</p>
                            <a href="{{ route('departments.show', $spotlight) }}" class="btn btn-orange">Buy now</a>
                        </div>
                        <i class="bi bi-phone promo-art" aria-hidden="true"></i>
                    </section>
                </div>
            @endif
        </div>

        {{-- Product sliders (managed in Admin → Homepage sliders) --}}
        @foreach ($sliders as $slider)
            <x-product-slider :title="$slider['title']" :icon="$slider['icon']" :products="$slider['products']" :view-all="$slider['viewAll']" />
        @endforeach

        {{-- Member sign-up (guests) --}}
        @guest
            <section class="member-cta row align-items-center g-4 justify-content-center" aria-labelledby="member-title">
                <div class="col-md-4 text-center text-md-end">
                    <i class="bi bi-bag-heart member-art" aria-hidden="true"></i>
                </div>
                <div class="col-md-7">
                    <p class="lead-q">Want to have the <strong>ultimate customer experience?</strong></p>
                    <h2 class="big" id="member-title">Become a <strong>member</strong> today!</h2>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4">Sign up <i class="bi bi-chevron-right small"></i></a>
                </div>
            </section>
        @endguest

    </div>
</x-layouts.store>
