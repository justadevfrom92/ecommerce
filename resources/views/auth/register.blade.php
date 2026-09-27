<x-layouts.store title="Sign up">
    <div class="container-xxl">
        <div class="auth-wrap">
            <img src="{{ asset('images/logo.svg') }}" alt="" width="56" height="56" class="auth-logo">
            <div class="text-center mb-4">
                <h1>Sign Up</h1>
                <p class="text-muted-2 mb-0">Create your account today</p>
            </div>

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label-caps d-block mb-1">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Your full name" required autofocus autocomplete="name">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label-caps d-block mb-1">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="name@example.com" required autocomplete="email">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label for="password" class="form-label-caps d-block mb-1">Password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-6">
                        <label for="password_confirmation" class="form-label-caps d-block mb-1">Confirm password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password" required autocomplete="new-password">
                    </div>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="newsletter" value="1" id="newsletter" @checked(old('newsletter'))>
                    <label class="form-check-label small fw-semibold" for="newsletter">Email me news and offers</label>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" value="1" id="terms" @checked(old('terms')) required>
                    <label class="form-check-label small fw-semibold" for="terms">I accept the terms and privacy policy</label>
                    @error('terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-3">Sign up</button>
                <div class="text-center"><a href="{{ route('login') }}" class="small fw-bold">Sign in to an existing account</a></div>
            </form>
        </div>
    </div>
</x-layouts.store>
