<x-layouts.store title="Forgot password">
    <div class="container-xxl py-4">
        <div class="card auth-card mx-auto shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Forgot your password?</h1>
                <p class="text-body-secondary small mb-4">Enter your email and we'll send you a link to choose a new one.</p>
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Email reset link</button>
                </form>
                <p class="text-center small mt-4 mb-0"><a href="{{ route('login') }}">Back to log in</a></p>
            </div>
        </div>
    </div>
</x-layouts.store>
