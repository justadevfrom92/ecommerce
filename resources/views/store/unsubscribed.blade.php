<x-layouts.store title="Unsubscribed">
    <div class="container-xxl">
        <div class="auth-wrap text-center">
            <i class="bi bi-envelope-slash fs-1 text-muted-2 d-block mb-3"></i>
            <h1>You've been unsubscribed</h1>
            <p class="text-muted-2">{{ $subscriber->email }} won't receive newsletters from {{ setting('store_name') }} any more.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Back to the store</a>
        </div>
    </div>
</x-layouts.store>
