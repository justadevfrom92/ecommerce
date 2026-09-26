<x-layouts.store title="Unsubscribed">
    <div class="container-xxl py-5 text-center">
        <i class="bi bi-envelope-slash fs-1 text-body-secondary d-block mb-3"></i>
        <h1 class="h4">You've been unsubscribed</h1>
        <p class="text-body-secondary">{{ $subscriber->email }} won't receive newsletters from {{ setting('store_name') }} any more.</p>
        <a href="{{ route('home') }}" class="btn btn-primary">Back to the store</a>
    </div>
</x-layouts.store>
