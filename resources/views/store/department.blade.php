<x-layouts.store :title="$department->name">
    <div class="container-xxl py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $department->name }}</li>
            </ol>
        </nav>
        <div class="hero p-4 mb-4">
            <h1 class="h2 mb-1">{{ $department->name }}</h1>
            @if ($department->description)
                <p class="text-body-secondary mb-3">{{ $department->description }}</p>
            @endif
            <div class="d-flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="btn btn-sm btn-light border">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>

        <x-product-slider :title="'Featured in '.$department->name" :products="$featured" />

        @foreach ($sliders as $slider)
            <x-product-slider :title="$slider['category']->name" :products="$slider['products']" :view-all="route('categories.show', $slider['category'])" />
        @endforeach

        @if ($sliders->isEmpty() && $featured->isEmpty())
            <p class="text-body-secondary">No products in this department yet.</p>
        @endif
    </div>
</x-layouts.store>
