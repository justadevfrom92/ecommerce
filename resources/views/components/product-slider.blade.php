{{--
    Horizontal product slider (CSS scroll-snap + public/js/app.js, no library).
    <x-product-slider title="Electronics" :products="$products" :view-all="route('departments.show', $dept)" />
--}}
@props(['title', 'products', 'viewAll' => null, 'subtitle' => null])
@if ($products->isNotEmpty())
    @php($id = 'slider-'.\Illuminate\Support\Str::slug($title).'-'.\Illuminate\Support\Str::random(4))
    <section {{ $attributes->class('product-slider mb-5') }} data-slider aria-labelledby="{{ $id }}-title">
        <div class="d-flex align-items-end justify-content-between mb-3 gap-3">
            <div>
                <h2 class="h4 mb-0" id="{{ $id }}-title">{{ $title }}</h2>
                @if ($subtitle)
                    <p class="text-body-secondary small mb-0">{{ $subtitle }}</p>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                @if ($viewAll)
                    <a href="{{ $viewAll }}" class="small me-2">View all</a>
                @endif
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle slider-btn" data-slider-prev aria-controls="{{ $id }}" aria-label="Previous products"><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle slider-btn" data-slider-next aria-controls="{{ $id }}" aria-label="Next products"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
        <div class="slider-track" id="{{ $id }}" data-slider-track tabindex="0" role="region" aria-label="{{ $title }} products">
            @foreach ($products as $product)
                <div class="slider-item">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>
    </section>
@endif
