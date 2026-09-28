@php
    $links = [];
    foreach ($siblings as $sibling) {
        $links[$sibling->name] = [route('categories.show', $sibling), $sibling->is($category)];
    }
@endphp
<x-layouts.store :title="$category->name">
    <div class="container-xxl py-4">
        <div class="row g-4 g-xl-5">
            <aside class="col-lg-3 col-xl-2">
                @include('store._filters', ['links' => $links, 'linksTitle' => $category->department->name])
            </aside>
            <div class="col-lg-9 col-xl-10">
                @include('store._grid')
            </div>
        </div>
    </div>
</x-layouts.store>
