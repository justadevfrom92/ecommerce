{{-- Account dropdown in the top-right: login popup for guests, account links when signed in. --}}
@php($loginErrors = $errors->getBag('login'))
<div class="dropdown">
    <button class="nav-icon-btn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
            aria-expanded="false" aria-label="Account" @if ($loginErrors->any()) data-open-on-load @endif>
        <i class="bi bi-person"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow-sm account-menu p-0">
        @guest
            <form method="POST" action="{{ route('login') }}" class="p-3">
                @csrf
                @if ($loginErrors->any())
                    <div class="alert alert-danger py-2 small">{{ $loginErrors->first() }}</div>
                @endif
                <label for="nav-email" class="form-label-caps d-block mb-1 ps-0">Email address</label>
                <div class="input-icon mb-2">
                    <i class="bi bi-person-fill" aria-hidden="true"></i>
                    <input type="email" class="form-control form-control-sm" id="nav-email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autocomplete="email">
                </div>
                <label for="nav-password" class="form-label-caps d-block mb-1 ps-0">Password</label>
                <div class="input-icon mb-2">
                    <i class="bi bi-key-fill" aria-hidden="true"></i>
                    <input type="password" class="form-control form-control-sm" id="nav-password" name="password" placeholder="Password" required autocomplete="current-password">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check small mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="nav-remember">
                        <label class="form-check-label" for="nav-remember">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="small fw-semibold">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">Log in</button>
            </form>
            <div class="border-top px-3 py-2 small text-center">
                New here? <a href="{{ route('register') }}" class="fw-bold">Create an account</a>
            </div>
        @else
            <div class="d-flex align-items-center gap-2 px-3 py-3 border-bottom">
                <span class="avatar-sm">{{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('') }}</span>
                <div class="min-w-0">
                    <div class="fw-bold text-truncate" style="color: var(--ms-heading)">{{ auth()->user()->name }}</div>
                    <div class="small text-muted-2 text-truncate">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <div class="py-2">
                <a class="dropdown-item" href="{{ route('account.edit') }}"><i class="bi bi-person me-2"></i>Profile</a>
                <a class="dropdown-item" href="{{ route('account.orders.index') }}"><i class="bi bi-bag me-2"></i>My orders</a>
                @can('admin.access')
                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Admin</a>
                @endcan
            </div>
            <div class="border-top p-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-soft btn-sm w-100"><i class="bi bi-box-arrow-right me-1"></i>Log out</button>
                </form>
            </div>
        @endguest
    </div>
</div>
