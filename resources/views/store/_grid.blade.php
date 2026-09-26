{{-- Product grid with sort / filter toolbar and shared pagination. Expects $products (paginator). --}}
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <p class="small text-body-secondary mb-0">{{ number_format($products->total()) }} {{ Str::plural('product', $products->total()) }}</p>
    <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
        @foreach (request()->only(['q', 'department']) as $k => $v)
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach
        <div class="form-check form-check-inline small mb-0">
            <input class="form-check-input" type="checkbox" name="on_sale" value="1" id="f-sale" @checked(request()->boolean('on_sale')) data-auto-submit>
            <label class="form-check-label" for="f-sale">On sale</label>
        </div>
        <div class="form-check form-check-inline small mb-0">
            <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="f-stock" @checked(request()->boolean('in_stock')) data-auto-submit>
            <label class="form-check-label" for="f-stock">In stock</label>
        </div>
        <label for="sort" class="visually-hidden">Sort by</label>
        <select id="sort" name="sort" class="form-select form-select-sm w-auto" data-auto-submit>
            @foreach (\App\Http\Controllers\CatalogController::SORTS as $value => $label)
                <option value="{{ $value }}" @selected(request('sort', 'newest') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <noscript><button class="btn btn-sm btn-outline-secondary">Apply</button></noscript>
    </form>
</div>

@if ($products->isEmpty())
    <div class="text-center text-body-secondary py-5">
        <i class="bi bi-search fs-1 d-block mb-3"></i>
        <p class="mb-1">No products match your search.</p>
        <a href="{{ route('search') }}">Browse all products</a>
    </div>
@else
    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 row-cols-xl-5 g-3 mb-4">
        @foreach ($products as $product)
            <div class="col"><x-product-card :product="$product" /></div>
        @endforeach
    </div>
    <x-pagination :paginator="$products" label="products" />
@endif
