<x-layouts.store title="Log in">
    <div class="container-xxl py-4">
        <div class="card auth-card mx-auto shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Log in</h1>
                <p class="text-body-secondary small mb-4">Welcome back. Sign in to see your orders and check out faster.</p>

                @php($loginErrors = $errors->getBag('login'))
                @if ($loginErrors->any() || $errors->any())
                    <div class="alert alert-danger py-2 small">{{ $loginErrors->first() ?: $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label for="password" class="form-label">Password</label>
                            <a href="{{ route('password.request') }}" class="small">Forgot password?</a>
                        </div>
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Log in</button>
                </form>
                <p class="text-center small mt-4 mb-0">Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
            </div>
        </div>
    </div>
</x-layouts.store>
