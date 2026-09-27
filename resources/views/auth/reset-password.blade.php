<x-layouts.store title="Reset password">
    <div class="container-xxl">
        <div class="auth-wrap">
            <img src="{{ asset('images/logo.svg') }}" alt="" width="56" height="56" class="auth-logo">
            <div class="text-center mb-4">
                <h1>Reset new password</h1>
                <p class="text-muted-2 mb-0">Type your new password</p>
            </div>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-3">
                    <label for="email" class="form-label-caps d-block mb-1">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label-caps d-block mb-1">New password</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label-caps d-block mb-1">Confirm new password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Set password</button>
            </form>
        </div>
    </div>
</x-layouts.store>
