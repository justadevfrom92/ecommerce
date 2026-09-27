<x-layouts.store title="My account">
    <div class="container-xxl py-4">
        @include('account._header')
        <div class="panel panel-body" style="max-width: 48rem">
            <h2 class="h5 fw-extrabold mb-3">Personal info</h2>
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
                <div class="col-12"><button type="submit" class="btn btn-primary px-4">Save changes</button></div>
            </form>
        </div>
    </div>
</x-layouts.store>
