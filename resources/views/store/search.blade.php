<x-layouts.store :title="$term !== '' ? 'Search: '.$term : 'Shop all'">
    <div class="container-xxl py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $term !== '' ? 'Search' : 'Shop all' }}</li>
            </ol>
        </nav>
        <h1 class="h3 mb-3">
            @if ($term !== '') Results for “{{ $term }}” @else {{ request()->boolean('on_sale') ? 'On sale' : 'All products' }} @endif
        </h1>

        <div class="row g-4">
            <aside class="col-lg-2">
                <h2 class="h6 text-uppercase text-body-secondary small">Departments</h2>
                <div class="list-group list-group-flush small">
                    <a href="{{ request()->fullUrlWithQuery(['department' => null, 'page' => null]) }}" class="list-group-item list-group-item-action px-0 border-0 @if (! $activeDepartment) fw-semibold text-primary @endif">All departments</a>
                    @foreach ($departments as $dept)
                        <a href="{{ request()->fullUrlWithQuery(['department' => $dept->slug, 'page' => null]) }}" class="list-group-item list-group-item-action px-0 border-0 @if ($activeDepartment?->is($dept)) fw-semibold text-primary @endif">{{ $dept->name }}</a>
                    @endforeach
                </div>
            </aside>
            <div class="col-lg-10">
                @include('store._grid')
            </div>
        </div>
    </div>
</x-layouts.store>
