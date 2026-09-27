<x-layouts.store title="My account">
    <div class="container-xxl py-4">
        @include('account._header')
        <form method="POST" action="{{ route('account.update') }}" style="max-width: 56rem">
            @csrf @method('PUT')

            <section class="panel panel-body mb-3">
                <h2 class="h5 fw-extrabold mb-3">Personal info</h2>
                <div class="row g-3">
                    <x-form.input class="col-md-6 mb-0" name="name" label="Full name" :value="$user->name" required autocomplete="name" />
                    <x-form.input class="col-md-6 mb-0" name="email" label="Email" type="email" :value="$user->email" required autocomplete="email" />
                </div>
            </section>

            <section class="panel panel-body mb-3" id="phone">
                <h2 class="h5 fw-extrabold mb-3">Phone</h2>
                <div class="row g-3">
                    <x-form.input class="col-md-6 mb-0" name="phone" label="Phone number" type="tel" :value="$user->phone" autocomplete="tel" />
                </div>
            </section>

            <section class="panel panel-body mb-3" id="address">
                <h2 class="h5 fw-extrabold mb-3">Address</h2>
                <div class="row g-3">
                    <x-form.input class="col-12 mb-0" name="address_line1" label="Address" :value="$user->address_line1" autocomplete="address-line1" />
                    <x-form.input class="col-12 mb-0" name="address_line2" label="Apartment, suite, etc." :value="$user->address_line2" autocomplete="address-line2" />
                    <x-form.input class="col-md-5 mb-0" name="city" label="City" :value="$user->city" autocomplete="address-level2" />
                    <x-form.input class="col-md-4 mb-0" name="state" label="State / region" :value="$user->state" autocomplete="address-level1" />
                    <x-form.input class="col-md-3 mb-0" name="postal_code" label="Postal code" :value="$user->postal_code" autocomplete="postal-code" />
                    <x-form.input class="col-md-4 mb-0" name="country" label="Country code" :value="$user->country" maxlength="2" placeholder="US" autocomplete="country" />
                </div>
            </section>

            <button type="submit" class="btn btn-primary px-4">Save changes</button>
        </form>
    </div>
</x-layouts.store>
