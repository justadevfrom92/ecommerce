<x-layouts.store title="Change password">
    <div class="container-xxl py-4">
        <h1 class="h3 mb-4">My account</h1>
        <div class="row g-4">
            <div class="col-lg-3">@include('account._nav')</div>
            <div class="col-lg-9">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Change password</h2>
                        <form method="POST" action="{{ route('account.password.update') }}" class="row g-3" style="max-width: 28rem">
                            @csrf @method('PUT')
                            <div class="col-12">
                                <label for="current_password" class="form-label">Current password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required autocomplete="current-password">
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="password" class="form-label">New password</label>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="password_confirmation" class="form-label">Confirm new password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password">
                            </div>
                            <div class="col-12"><button type="submit" class="btn btn-primary">Update password</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.store>
