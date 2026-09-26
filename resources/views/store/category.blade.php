<x-layouts.store :title="$category->name">
    <div class="container-xxl py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('departments.show', $category->department) }}">{{ $category->department->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
            </ol>
        </nav>
        <h1 class="h3 mb-1">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="text-body-secondary">{{ $category->description }}</p>
        @endif

        <ul class="nav nav-pills small gap-1 my-3">
            @foreach ($siblings as $sibling)
                <li class="nav-item"><a href="{{ route('categories.show', $sibling) }}" class="nav-link py-1 @if ($sibling->is($category)) active @else bg-body-tertiary text-body @endif">{{ $sibling->name }}</a></li>
            @endforeach
        </ul>

        @include('store._grid')
    </div>
</x-layouts.store>
