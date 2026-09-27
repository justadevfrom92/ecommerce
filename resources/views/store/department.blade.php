<x-layouts.store :title="$department->name">
    <div class="container-xxl py-4">
        <h1 class="page-title">{{ $department->name }}</h1>
        @if ($department->description)
            <p class="text-muted-2 mt-1 mb-4">{{ $department->description }}</p>
        @endif


        <x-product-slider :title="'Top deals in '.$department->name" :products="$featured" />

        @foreach ($sliders as $slider)
            <x-product-slider :title="'Top '.$slider['category']->name" :products="$slider['products']" :view-all="route('categories.show', $slider['category'])" />
        @endforeach

        @if ($sliders->isEmpty() && $featured->isEmpty())
            <p class="text-muted-2">No products in this department yet.</p>
        @endif
    </div>
</x-layouts.store>
