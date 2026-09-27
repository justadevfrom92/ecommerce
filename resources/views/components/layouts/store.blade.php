@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="d-flex flex-column min-vh-100">
<a class="visually-hidden-focusable" href="#main">Skip to content</a>

<header class="site-header">
    {{-- Row 1: logo icon (home) · search · account + cart --}}
    <div class="site-topbar">
        <nav class="container-xxl d-flex flex-wrap align-items-center gap-2 gap-md-4 py-2" aria-label="Main">
            <a href="{{ route('home') }}" class="site-logo flex-shrink-0" aria-label="{{ setting('store_name') }} home">
                <img src="{{ asset('images/logo.svg') }}" alt="{{ setting('store_name') }}" width="36" height="36">
            </a>

            <form action="{{ route('search') }}" method="GET" role="search" class="site-search order-last order-md-0 flex-grow-1 mx-md-auto">
                <div class="search-pill">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="q" class="form-control" placeholder="Search products" value="{{ request()->routeIs('search') ? request('q') : '' }}" aria-label="Search products">
                </div>
            </form>

            <div class="d-flex align-items-center gap-1 ms-auto ms-md-0">
                <a href="{{ route('cart.index') }}" class="nav-icon-btn position-relative" aria-label="Cart, {{ $cartCount }} items">
                    <i class="bi bi-cart3"></i>
                    @if ($cartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill cart-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    @endif
                </a>
                @include('partials.account-menu')
            </div>
        </nav>
    </div>

    {{-- Row 2: Category menu · page links --}}
    <div class="site-navbar">
        <div class="container-xxl d-flex align-items-center gap-3">
            @if ($navDepartments !== [])
                <div class="dropdown flex-shrink-0">
                    <button class="category-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-list me-1"></i>Category
                    </button>
                    <div class="dropdown-menu category-menu shadow-sm">
                        <div class="row g-4">
                            @foreach ($navDepartments as $dept)
                                <div class="col-6 col-md-3">
                                    <h6><a href="{{ route('departments.show', $dept['slug']) }}">{{ $dept['name'] }}</a></h6>
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($dept['categories'] as $cat)
                                            <li><a href="{{ route('categories.show', $cat['slug']) }}">{{ $cat['name'] }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            <ul class="nav flex-nowrap overflow-auto ms-auto navbar-links">
                <li class="nav-item"><a class="nav-link text-nowrap @if (request()->routeIs('home')) active @endif" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap @if (request()->routeIs('search') && ! request('on_sale')) active @endif" href="{{ route('search') }}">Products</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap @if (request('on_sale')) active @endif" href="{{ route('search', ['on_sale' => 1]) }}">Deals</a></li>
                @foreach (array_slice($navDepartments, 0, 4) as $dept)
                    <li class="nav-item d-none d-lg-block"><a class="nav-link text-nowrap @if (request()->is('departments/'.$dept['slug'])) active @endif" href="{{ route('departments.show', $dept['slug']) }}">{{ $dept['name'] }}</a></li>
                @endforeach
                <li class="nav-item"><a class="nav-link text-nowrap @if (request()->routeIs('account.orders.*')) active @endif" href="{{ auth()->check() ? route('account.orders.index') : route('login') }}">Track order</a></li>
                <li class="nav-item"><a class="nav-link text-nowrap @if (request()->routeIs('checkout.*')) active @endif" href="{{ route('checkout.create') }}">Checkout</a></li>
            </ul>
        </div>
    </div>
</header>

<main id="main" class="flex-grow-1">
    @if (session('status') || session('error'))
        <div class="container-xxl pt-3">
            @include('partials.flash')
        </div>
    @endif
    {{ $slot }}
</main>

<footer class="site-footer mt-5">
    <div class="container-xxl d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 py-3">
        <nav aria-label="Footer">
            <ul class="nav">
                <li class="nav-item"><a class="nav-link ps-0" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('search') }}">Shop all</a></li>
                @if (Route::has('contact'))
                    <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
                @endif
                <li class="nav-item"><a class="nav-link" href="{{ auth()->check() ? route('account.edit') : route('login') }}">My account</a></li>
            </ul>
        </nav>
        <p class="mb-0 copyright">&copy; {{ date('Y') }} {{ setting('store_name') }}</p>
    </div>
</footer>

@include('partials.scripts')
@stack('scripts')
</body>
</html>
