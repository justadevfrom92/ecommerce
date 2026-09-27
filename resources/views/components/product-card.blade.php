{{-- Product tile: bordered image box, title, note, price row. --}}
@props(['product'])
<article {{ $attributes->class('product-card') }}>
    <a href="{{ route('products.show', $product) }}" class="product-card-image" tabindex="-1" aria-hidden="true">
        <img src="{{ $product->imageUrl() }}" alt="" loading="lazy">
        @if ($product->isOnSale())
            <span class="pc-flag pill pill-danger">{{ round((1 - $product->price / $product->compare_at_price) * 100) }}% off</span>
        @elseif ($product->is_featured && $product->inStock())
            <span class="pc-flag pill pill-success">Featured <i class="bi bi-check-lg"></i></span>
        @endif
    </a>
    <div class="pc-body">
        <h3 class="pc-title"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        @if ($product->relationLoaded('category') && $product->category)
            <div class="pc-meta">{{ $product->category->name }}</div>
        @endif
        {{-- Stock note sits in a fixed slot above the price so prices line up across a row. --}}
        <div class="pc-status">
            @if (! $product->inStock())
                <span class="pc-out">Out of stock</span>
            @elseif ($product->stock <= 5)
                <span class="pc-deal">Only {{ $product->stock }} left</span>
            @endif
        </div>
        <div class="pc-price">
            @if ($product->isOnSale())
                <span class="price-old">{{ money($product->compare_at_price) }}</span>
            @endif
            <span class="price-now">{{ money($product->price) }}</span>
        </div>
        @if ($product->inStock())
            <form method="POST" action="{{ route('cart.store') }}" class="pc-actions">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="btn btn-outline-primary btn-sm" aria-label="Add {{ $product->name }} to cart"><i class="bi bi-cart-plus"></i><span class="ms-1">Add</span></button>
                <button type="submit" formaction="{{ route('cart.buy-now') }}" class="btn btn-primary btn-sm" aria-label="Quick pay for {{ $product->name }}">Quick pay</button>
            </form>
        @endif
    </div>
</article>
