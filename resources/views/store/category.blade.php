@php
    $links = [];
    foreach ($siblings as $sibling) {
        $links[$sibling->name] = [route('categories.show', $sibling), $sibling->is($category)];
    }
@endphp
<x-layouts.store :title="$category->name">
    <div class="container-xxl py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('departments.show', $category->department) }}">{{ $category->department->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
            </ol>
        </nav>
        <h1 class="page-title">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="text-muted-2 mt-1">{{ $category->description }}</p>
        @endif

        <div class="row g-4 g-xl-5 mt-1">
            <aside class="col-lg-3 col-xl-2">
                @include('store._filters', ['links' => $links, 'linksTitle' => $category->department->name])
            </aside>
            <div class="col-lg-9 col-xl-10">
                @include('store._grid')
            </div>
        </div>
    </div>
</x-layouts.store>
