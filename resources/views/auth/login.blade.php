<x-layouts.store title="Log in">
    <div class="container-xxl">
        <div class="auth-wrap">
            <img src="{{ asset('images/logo.svg') }}" alt="" width="56" height="56" class="auth-logo">
            <div class="text-center mb-4">
                <h1>Sign In</h1>
                <p class="text-muted-2 mb-0">Get access to your account</p>
            </div>

            @php($loginErrors = $errors->getBag('login'))
            @if ($loginErrors->any() || $errors->any())
                <div class="alert alert-danger py-2 small">{{ $loginErrors->first() ?: $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <label for="email" class="form-label-caps d-block mb-1">Email address</label>
                <div class="input-icon mb-3">
                    <i class="bi bi-person-fill" aria-hidden="true"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@example.com" required autofocus autocomplete="email">
                </div>
                <label for="password" class="form-label-caps d-block mb-1">Password</label>
                <div class="input-icon mb-3">
                    <i class="bi bi-key-fill" aria-hidden="true"></i>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required autocomplete="current-password">
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                        <label class="form-check-label small fw-semibold" for="remember">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="small fw-semibold">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
                <div class="text-center"><a href="{{ route('register') }}" class="small fw-bold">Create an account</a></div>
            </form>
        </div>
    </div>
</x-layouts.store>
