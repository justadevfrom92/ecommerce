<x-layouts.store title="My account">
    <div class="container-xxl py-4">
        <h1 class="h3 mb-4">My account</h1>
        <div class="row g-4">
            <div class="col-lg-3">@include('account._nav')</div>
            <div class="col-lg-9">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Profile</h2>
                        <form method="POST" action="{{ route('account.update') }}" class="row g-3">
                            @csrf @method('PUT')
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full name</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.store>
