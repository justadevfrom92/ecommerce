<x-layouts.store title="Reset password">
    <div class="container-xxl py-4">
        <div class="card auth-card mx-auto shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">Choose a new password</h1>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Reset password</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.store>
