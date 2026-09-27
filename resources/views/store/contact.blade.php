<x-layouts.store title="Contact us">
    <div class="container-xxl py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Contact</li>
            </ol>
        </nav>
        <h1 class="page-title mb-1">Contact us</h1>
        <p class="text-muted-2 mb-4">Questions about an order, a product or anything else? Send us a message and we'll reply by email.</p>
        <div class="row g-4 g-xl-5">
            <div class="col-lg-7">
                <div class="panel panel-body">
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
                        <button class="btn btn-primary px-4">Send message</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="panel panel-body">
                    <h2 class="h5 fw-extrabold mb-3">Other ways to reach us</h2>
                    <div class="detail-list">
                        <div class="detail-item"><i class="bi bi-envelope"></i><span class="k">Email</span><span class="v"><a href="mailto:{{ setting('store_email') }}">{{ setting('store_email') }}</a></span></div>
                        <div class="detail-item"><i class="bi bi-bag"></i><span class="k">Orders</span><span class="v">@auth Track them in <a href="{{ route('account.orders.index') }}">My orders</a>. @else <a href="{{ route('login') }}">Sign in</a> to track your orders. @endauth</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.store>
