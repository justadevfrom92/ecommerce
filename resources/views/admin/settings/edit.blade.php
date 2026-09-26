<x-layouts.admin title="Site settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="row g-4">
        @csrf @method('PUT')
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Store</h2>
                    <x-form.input name="store_name" label="Store name" :value="$settings['store_name']" required help="Used in the page title, footer copyright and emails." />
                    <x-form.input name="store_email" label="Store email" type="email" :value="$settings['store_email']" required help="Emails are sent from, and replies go to, this address." />
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">Money</h2>
                    <div class="row">
                        <x-form.input class="col-6" name="currency" label="Currency code" :value="strtoupper($settings['currency'])" required maxlength="3" help="ISO code Stripe charges in, e.g. USD, CAD, EUR." />
                        <x-form.input class="col-6" name="currency_symbol" label="Symbol" :value="$settings['currency_symbol']" required maxlength="5" />
                        <x-form.input class="col-md-4" name="shipping_flat_rate" label="Flat shipping" type="number" step="0.01" min="0" :value="$settings['shipping_flat_rate']" required />
                        <x-form.input class="col-md-4" name="free_shipping_over" label="Free shipping over" type="number" step="0.01" min="0" :value="$settings['free_shipping_over']" required help="0 = never free." />
                        <x-form.input class="col-md-4" name="tax_rate" label="Tax rate %" type="number" step="0.001" min="0" max="100" :value="$settings['tax_rate']" required />
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12"><button class="btn btn-primary">Save settings</button></div>
    </form>
</x-layouts.admin>
