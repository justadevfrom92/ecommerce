{{-- Product tile: one white card — image, two-line title, category/stock, price, Add + Quick pay. --}}
@props(['product'])
<article {{ $attributes->class('product-card') }}>
    <a href="{{ route('products.show', $product) }}" class="product-card-image" style="--tint: {{ $product->tint() }}" tabindex="-1" aria-hidden="true">
        <img src="{{ $product->imageUrl() }}" alt="" loading="lazy">
        @if ($product->isOnSale())
            <span class="pc-flag pill pill-danger">{{ round((1 - $product->price / $product->compare_at_price) * 100) }}% off</span>
        @elseif ($product->is_featured && $product->inStock())
            <span class="pc-flag pill pill-success">Featured</span>
        @endif
    </a>
    <div class="pc-body">
        <h3 class="pc-title"><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
        <div class="pc-meta">
            @if ($product->relationLoaded('category') && $product->category)
                <span>{{ $product->category->name }}</span>
            @endif
            @if (! $product->inStock())
                <span class="pc-out">Out of stock</span>
            @elseif ($product->stock <= 5)
                <span class="pc-deal">Only {{ $product->stock }} left</span>
            @endif
        </div>
        <div class="pc-price">
            <span @class(['price-now', 'is-sale' => $product->isOnSale()])>{{ money($product->price) }}</span>
            @if ($product->isOnSale())
                <span class="price-old">{{ money($product->compare_at_price) }}</span>
            @endif
        </div>
        @if ($product->inStock())
            <form method="POST" action="{{ route('cart.store') }}" class="pc-actions">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button type="submit" class="btn btn-outline-primary btn-sm" aria-label="Add {{ $product->name }} to cart">Add to cart</button>
                <button type="submit" formaction="{{ route('cart.buy-now') }}" class="btn btn-primary btn-sm" aria-label="Quick pay for {{ $product->name }}">Quick pay</button>
            </form>
        @endif
    </div>
</article>
