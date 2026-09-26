@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="d-flex flex-column min-vh-100">
<a class="visually-hidden-focusable" href="#main">Skip to content</a>

<header class="site-header sticky-top bg-body border-bottom">
    <nav class="container-xxl d-flex flex-wrap align-items-center gap-2 gap-md-3 py-2" aria-label="Main">
        <a href="{{ route('home') }}" class="site-logo flex-shrink-0" aria-label="{{ setting('store_name') }} home">
            <img src="{{ asset('images/logo.svg') }}" alt="{{ setting('store_name') }}" width="40" height="40">
        </a>

        <form action="{{ route('search') }}" method="GET" role="search" class="site-search order-last order-md-0 flex-grow-1">
            <div class="input-group">
                <input type="search" name="q" class="form-control" placeholder="Search products…" value="{{ request()->routeIs('search') ? request('q') : '' }}" aria-label="Search products">
                <button class="btn btn-primary" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <div class="d-flex align-items-center gap-1 ms-auto">
            @include('partials.account-menu')
            <a href="{{ route('cart.index') }}" class="btn btn-link nav-icon-btn position-relative" aria-label="Cart, {{ $cartCount }} items">
                <i class="bi bi-cart3"></i>
                @if ($cartCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                @endif
            </a>
        </div>
    </nav>

    @if ($navDepartments !== [])
        <div class="department-bar border-top">
            <div class="container-xxl">
                <ul class="nav flex-nowrap overflow-auto small">
                    @foreach ($navDepartments as $dept)
                        <li class="nav-item"><a class="nav-link text-nowrap @if (request()->is('departments/'.$dept['slug'])) active @endif" href="{{ route('departments.show', $dept['slug']) }}">{{ $dept['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</header>

<main id="main" class="flex-grow-1">
    <div class="container-xxl pt-3">
        @include('partials.flash')
    </div>
    {{ $slot }}
</main>

<footer class="site-footer border-top mt-5">
    <div class="container-xxl d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 py-3 small">
        <nav aria-label="Footer">
            <ul class="nav">
                <li class="nav-item"><a class="nav-link px-2 text-body-secondary" href="{{ route('home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link px-2 text-body-secondary" href="{{ route('search') }}">Shop all</a></li>
                @if (Route::has('contact'))
                    <li class="nav-item"><a class="nav-link px-2 text-body-secondary" href="{{ route('contact') }}">Contact</a></li>
                @endif
                <li class="nav-item"><a class="nav-link px-2 text-body-secondary" href="{{ auth()->check() ? route('account.edit') : route('login') }}">My account</a></li>
            </ul>
        </nav>
        <p class="mb-0 text-body-secondary">&copy; {{ date('Y') }} {{ setting('store_name') }}</p>
    </div>
</footer>

@include('partials.scripts')
@stack('scripts')
</body>
</html>
