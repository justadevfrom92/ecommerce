@php
    $links = ['All departments' => [request()->fullUrlWithQuery(['department' => null, 'page' => null]), ! $activeDepartment]];
    foreach ($departments as $dept) {
        $links[$dept->name] = [request()->fullUrlWithQuery(['department' => $dept->slug, 'page' => null]), (bool) $activeDepartment?->is($dept)];
    }
@endphp
<x-layouts.store :title="$term !== '' ? 'Search: '.$term : 'Shop all'">
    <div class="container-xxl py-4">
        <h1 class="page-title mb-4">
            @if ($term !== '') Results for “{{ $term }}” @else {{ request()->boolean('on_sale') ? 'Deals' : 'All products' }} @endif
        </h1>

        <div class="row g-4 g-xl-5">
            <aside class="col-lg-3 col-xl-2">
                @include('store._filters', ['links' => $links, 'linksTitle' => 'Departments'])
            </aside>
            <div class="col-lg-9 col-xl-10">
                @include('store._grid')
            </div>
        </div>
    </div>
</x-layouts.store>
