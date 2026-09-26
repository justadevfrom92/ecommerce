@props(['product'])
<div {{ $attributes->class('card product-card h-100 border-0 shadow-sm') }}>
    <a href="{{ route('products.show', $product) }}" class="product-card-image ratio ratio-1x1 bg-body-tertiary rounded-top">
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" class="object-fit-cover rounded-top">
    </a>
    @if ($product->isOnSale())
        <span class="badge bg-danger position-absolute top-0 start-0 m-2">Sale</span>
    @endif
    <div class="card-body d-flex flex-column p-3">
        @if ($product->relationLoaded('category') && $product->category)
            <div class="small text-body-secondary text-truncate mb-1">{{ $product->category->name }}</div>
        @endif
        <h3 class="h6 card-title mb-2 product-card-title">
            <a href="{{ route('products.show', $product) }}" class="stretched-link text-reset text-decoration-none">{{ $product->name }}</a>
        </h3>
        <div class="mt-auto d-flex align-items-baseline gap-2">
            <span class="fw-bold">{{ money($product->price) }}</span>
            @if ($product->isOnSale())
                <span class="small text-body-secondary text-decoration-line-through">{{ money($product->compare_at_price) }}</span>
            @endif
        </div>
        @unless ($product->inStock())
            <div class="small text-danger mt-1">Out of stock</div>
        @endunless
    </div>
</div>
