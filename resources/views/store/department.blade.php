<x-layouts.store :title="$department->name">
    <div class="container-xxl py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $department->name }}</li>
            </ol>
        </nav>
        <h1 class="page-title">{{ $department->name }}</h1>
        @if ($department->description)
            <p class="text-muted-2 mt-1 mb-3">{{ $department->description }}</p>
        @endif

        <nav class="cat-tiles my-4" aria-label="{{ $department->name }} categories">
            @foreach ($categories as $category)
                <a href="{{ route('categories.show', $category) }}" class="cat-tile">
                    <span class="cat-icon"><i class="bi bi-{{ \App\Http\Controllers\HomeController::iconFor($category->name) }}"></i></span>{{ $category->name }}
                </a>
            @endforeach
        </nav>

        <x-product-slider :title="'Top deals in '.$department->name" icon="lightning-charge-fill" :products="$featured" />

        @foreach ($sliders as $slider)
            <x-product-slider :title="'Top '.$slider['category']->name" :products="$slider['products']" :view-all="route('categories.show', $slider['category'])" />
        @endforeach

        @if ($sliders->isEmpty() && $featured->isEmpty())
            <p class="text-muted-2">No products in this department yet.</p>
        @endif
    </div>
</x-layouts.store>
