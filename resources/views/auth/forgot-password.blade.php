<x-layouts.store title="Forgot password">
    <div class="container-xxl">
        <div class="auth-wrap">
            <img src="{{ asset('images/logo.svg') }}" alt="" width="56" height="56" class="auth-logo">
            <div class="text-center mb-4">
                <h1>Forgot your password?</h1>
                <p class="text-muted-2 mb-0">Enter your email and we'll send you a reset link.</p>
            </div>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="d-flex gap-2 mb-3">
                    <label for="email" class="visually-hidden">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="name@example.com" required autofocus>
                    <button type="submit" class="btn btn-primary text-nowrap">Send <i class="bi bi-chevron-right small"></i></button>
                </div>
                @error('email')<div class="small text-danger mb-2">{{ $message }}</div>@enderror
                <div class="text-center"><a href="{{ route('login') }}" class="small fw-bold">Back to sign in</a></div>
            </form>
        </div>
    </div>
</x-layouts.store>
