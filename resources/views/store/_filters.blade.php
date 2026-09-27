{{-- Filter sidebar for product grids. Expects $links: [label => [url, active]] with a $linksTitle. --}}
<form method="GET" class="filters" id="filters">
    @foreach (request()->only(['q', 'department']) as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach
    <h2 class="mb-3">Filters</h2>

    <fieldset class="filter-group">
        <legend>Availability</legend>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="f-stock" @checked(request()->boolean('in_stock')) data-auto-submit>
            <label class="form-check-label" for="f-stock">In stock</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="on_sale" value="1" id="f-sale" @checked(request()->boolean('on_sale')) data-auto-submit>
            <label class="form-check-label" for="f-sale">On sale</label>
        </div>
    </fieldset>

    <fieldset class="filter-group">
        <legend>Sort by</legend>
        @foreach (\App\Http\Controllers\CatalogController::SORTS as $value => $label)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="sort" value="{{ $value }}" id="f-sort-{{ $value }}" @checked(request('sort', 'newest') === $value) data-auto-submit>
                <label class="form-check-label" for="f-sort-{{ $value }}">{{ $label }}</label>
            </div>
        @endforeach
    </fieldset>

    @if (! empty($links))
        <div class="filter-group">
            <div class="filter-title">{{ $linksTitle }}</div>
            @foreach ($links as $label => [$url, $active])
                <a href="{{ $url }}" class="filter-link @if ($active) active @endif">{{ $label }}</a>
            @endforeach
        </div>
    @endif

    <noscript><button class="btn btn-soft btn-sm">Apply filters</button></noscript>
</form>
