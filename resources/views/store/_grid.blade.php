{{-- Product grid with shared pagination. Expects $products (paginator). --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <p class="showing mb-0"><strong>{{ number_format($products->total()) }}</strong> {{ Str::plural('product', $products->total()) }}</p>
</div>

@if ($products->isEmpty())
    <div class="panel panel-body text-center text-muted-2 py-5">
        <i class="bi bi-search fs-1 d-block mb-3"></i>
        <p class="mb-1 fw-bold" style="color: var(--ms-heading)">No products match your search.</p>
        <a href="{{ route('search') }}" class="fw-bold">Browse all products</a>
    </div>
@else
    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-4 mb-4">
        @foreach ($products as $product)
            <div class="col"><x-product-card :product="$product" /></div>
        @endforeach
    </div>
    <div class="border-top pt-3">
        <x-pagination :paginator="$products" label="products" />
    </div>
@endif
