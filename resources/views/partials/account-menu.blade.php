{{-- Account dropdown in the top-right: login popup for guests, account links when signed in. --}}
@php($loginErrors = $errors->getBag('login'))
<div class="dropdown">
    <button class="btn btn-link nav-icon-btn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
            aria-expanded="false" aria-label="Account" @if ($loginErrors->any()) data-open-on-load @endif>
        <i class="bi bi-person-circle"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow account-menu">
        @guest
            <form method="POST" action="{{ route('login') }}" class="px-3 py-2">
                @csrf
                <h6 class="mb-3">Sign in to your account</h6>
                @if ($loginErrors->any())
                    <div class="alert alert-danger py-2 small">{{ $loginErrors->first() }}</div>
                @endif
                <div class="mb-2">
                    <label for="nav-email" class="form-label small mb-1">Email</label>
                    <input type="email" class="form-control form-control-sm" id="nav-email" name="email" value="{{ old('email') }}" required autocomplete="email">
                </div>
                <div class="mb-2">
                    <label for="nav-password" class="form-label small mb-1">Password</label>
                    <input type="password" class="form-control form-control-sm" id="nav-password" name="password" required autocomplete="current-password">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check small">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="nav-remember">
                        <label class="form-check-label" for="nav-remember">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="small">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Log in</button>
            </form>
            <div class="dropdown-divider"></div>
            <div class="px-3 pb-2 small text-center">
                New here? <a href="{{ route('register') }}">Create an account</a>
            </div>
        @else
            <div class="px-3 py-2">
                <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                <div class="small text-body-secondary text-truncate">{{ auth()->user()->email }}</div>
            </div>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('account.edit') }}"><i class="bi bi-person me-2"></i>My account</a>
            @if (Route::has('account.orders.index'))
                <a class="dropdown-item" href="{{ route('account.orders.index') }}"><i class="bi bi-bag me-2"></i>My orders</a>
            @endif
            @can('admin.access')
                <a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Admin</a>
            @endcan
            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Log out</button>
            </form>
        @endguest
    </div>
</div>
