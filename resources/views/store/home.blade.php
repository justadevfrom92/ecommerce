<x-layouts.store>
    <div class="container-xxl">
        <section class="hero p-4 p-md-5 mb-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <p class="text-primary fw-semibold mb-2">New season, new arrivals</p>
                    <h1 class="display-5 fw-bold mb-3">Everything you need, delivered to your door.</h1>
                    <p class="lead text-body-secondary mb-4">Shop thousands of products across every department, with free shipping on orders over {{ money(setting('free_shipping_over')) }}.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('search') }}" class="btn btn-primary btn-lg">Shop all products</a>
                        <a href="{{ route('search', ['on_sale' => 1]) }}" class="btn btn-outline-secondary btn-lg">See the sale</a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block text-center">
                    <img src="{{ asset('images/logo.svg') }}" alt="" width="220" height="220" class="opacity-75">
                </div>
            </div>
        </section>

        @if ($departments->isNotEmpty())
            <section class="mb-5" aria-labelledby="departments-title">
                <h2 class="h4 mb-3" id="departments-title">Shop by department</h2>
                <div class="row row-cols-2 row-cols-md-4 g-3">
                    @foreach ($departments as $department)
                        <div class="col">
                            <a href="{{ route('departments.show', $department) }}" class="department-tile d-block p-4 h-100 text-reset text-decoration-none">
                                <div class="fw-semibold">{{ $department->name }}</div>
                                <div class="small text-body-secondary">{{ number_format($department->products_count) }} products</div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @foreach ($sliders as $slider)
            <x-product-slider :title="$slider['title']" :products="$slider['products']" :view-all="$slider['viewAll']" />
        @endforeach

        <section class="hero p-4 p-md-5 mt-2" aria-labelledby="newsletter-title">
            <div class="row align-items-center g-3">
                <div class="col-lg-6">
                    <h2 class="h4 mb-1" id="newsletter-title">Get the newsletter</h2>
                    <p class="text-body-secondary mb-0">New arrivals and member-only offers. No spam, and you can unsubscribe any time.</p>
                </div>
                <div class="col-lg-6">
                    <form method="POST" action="{{ route('newsletter.store') }}" class="d-flex gap-2">
                        @csrf
                        <label for="newsletter-email" class="visually-hidden">Email address</label>
                        <input type="email" id="newsletter-email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="you@example.com" value="{{ auth()->user()?->email }}" required>
                        <button class="btn btn-primary text-nowrap">Subscribe</button>
                    </form>
                    @error('email')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>
    </div>
</x-layouts.store>
