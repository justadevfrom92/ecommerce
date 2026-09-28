<x-layouts.store :title="$department->name">
    <div class="container-xxl py-4">
        <div class="page-banner banner-blue mb-4">
            <h1 class="page-title">{{ $department->name }}</h1>
            <p class="mb-0">{{ $department->description ?: 'Shop the best in '.$department->name.'.' }}</p>
        </div>


        <x-product-slider class="band-1" :title="'Top deals in '.$department->name" :products="$featured" />

        @foreach ($sliders as $slider)
            <x-product-slider :class="'band-'.(($loop->index + 2) % 5)" :title="'Top '.$slider['category']->name" :products="$slider['products']" :view-all="route('categories.show', $slider['category'])" />
        @endforeach

        @if ($sliders->isEmpty() && $featured->isEmpty())
            <p class="text-muted-2">No products in this department yet.</p>
        @endif
    </div>
</x-layouts.store>
