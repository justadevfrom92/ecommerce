<x-layouts.store title="Sign up">
    <div class="container-xxl py-4">
        <div class="card auth-card mx-auto shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Create your account</h1>
                <p class="text-body-secondary small mb-4">Track orders, save your details and get member-only offers.</p>

                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required autofocus autocomplete="name">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autocomplete="email">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password" aria-describedby="password-help">
                        <div id="password-help" class="form-text">At least 8 characters.</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="newsletter" value="1" id="newsletter" @checked(old('newsletter'))>
                        <label class="form-check-label small" for="newsletter">Email me news and offers</label>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" value="1" id="terms" @checked(old('terms')) required>
                        <label class="form-check-label small" for="terms">I agree to the terms and privacy policy</label>
                        @error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create account</button>
                </form>
                <p class="text-center small mt-4 mb-0">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
            </div>
        </div>
    </div>
</x-layouts.store>
