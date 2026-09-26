<x-layouts.store title="Contact us">
    <div class="container-xxl py-4">
        <div class="row g-4 justify-content-center">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h1 class="h4 mb-1">Contact us</h1>
                        <p class="text-body-secondary small mb-4">Questions about an order, a product or anything else? Send us a message and we'll reply by email.</p>
                        <form method="POST" action="{{ route('contact') }}">
                            @csrf
                            <div class="d-none" aria-hidden="true">
                                <label for="website">Leave this empty</label>
                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>
                            <div class="row">
                                <x-form.input class="col-md-6" name="name" label="Your name" :value="$user?->name" required autocomplete="name" />
                                <x-form.input class="col-md-6" name="email" label="Email" type="email" :value="$user?->email" required autocomplete="email" />
                            </div>
                            <x-form.input name="subject" label="Subject" required />
                            <x-form.textarea name="message" label="Message" rows="6" required />
                            <button class="btn btn-primary">Send message</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 bg-body-tertiary">
                    <div class="card-body p-4 small">
                        <h2 class="h6">Other ways to reach us</h2>
                        <p class="mb-2"><i class="bi bi-envelope me-2"></i><a href="mailto:{{ setting('store_email') }}">{{ setting('store_email') }}</a></p>
                        @auth
                            <p class="mb-0"><i class="bi bi-bag me-2"></i>Track orders in <a href="{{ route('account.orders.index') }}">My orders</a>.</p>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.store>
