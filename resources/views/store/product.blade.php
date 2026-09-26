<x-layouts.store :title="$product->name">
    <div class="container-xxl py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('departments.show', $product->category->department) }}">{{ $product->category->department->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('categories.show', $product->category) }}">{{ $product->category->name }}</a></li>
                <li class="breadcrumb-item active text-truncate" aria-current="page">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="row g-4 g-lg-5 mb-5">
            <div class="col-md-6">
                <div class="ratio ratio-1x1 bg-body-tertiary rounded-3 overflow-hidden">
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="object-fit-cover">
                </div>
            </div>
            <div class="col-md-6">
                <h1 class="h2 mb-2">{{ $product->name }}</h1>
                <p class="small text-body-secondary mb-3">SKU: {{ $product->sku }}</p>
                <div class="d-flex align-items-baseline gap-2 mb-3">
                    <span class="fs-3 fw-bold">{{ money($product->price) }}</span>
                    @if ($product->isOnSale())
                        <span class="text-body-secondary text-decoration-line-through">{{ money($product->compare_at_price) }}</span>
                        <span class="badge bg-danger">Save {{ round((1 - $product->price / $product->compare_at_price) * 100) }}%</span>
                    @endif
                </div>

                @if ($product->inStock())
                    <p class="text-success small mb-3"><i class="bi bi-check-circle me-1"></i>In stock @if ($product->stock <= 5)— only {{ $product->stock }} left @endif</p>
                    <form method="POST" action="{{ route('cart.store') }}" class="d-flex gap-2 mb-4" style="max-width: 22rem">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <label for="quantity" class="visually-hidden">Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="{{ min(99, $product->stock) }}" class="form-control" style="max-width: 6rem">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-cart-plus me-1"></i>Add to cart</button>
                    </form>
                @else
                    <p class="text-danger mb-4"><i class="bi bi-x-circle me-1"></i>Out of stock</p>
                @endif

                @if ($product->description)
                    <h2 class="h6">Description</h2>
                    <div class="text-body-secondary">{!! nl2br(e($product->description)) !!}</div>
                @endif
            </div>
        </div>

        <x-product-slider title="You may also like" :products="$related" :view-all="route('categories.show', $product->category)" />
        <x-product-slider :title="'More from '.$product->category->department->name" :products="$departmentPicks" :view-all="route('departments.show', $product->category->department)" />
    </div>
</x-layouts.store>
