{{--
    Horizontal product slider (CSS scroll-snap + public/js/app.js, no library).
    <x-product-slider title="Electronics" :products="$products" :view-all="route('departments.show', $dept)" />
--}}
@props(['title', 'products', 'viewAll' => null, 'subtitle' => null, 'icon' => null])
@if ($products->isNotEmpty())
    @php($id = 'slider-'.\Illuminate\Support\Str::slug($title).'-'.\Illuminate\Support\Str::random(4))
    <section {{ $attributes->class('product-slider mb-5') }} data-slider aria-labelledby="{{ $id }}-title">
        <div class="slider-head">
            <div>
                <h2 class="section-title" id="{{ $id }}-title">
                    @if ($icon)<i class="bi bi-{{ $icon }} text-warning"></i>@endif
                    {{ $title }}
                    @if ($icon)<i class="bi bi-{{ $icon }} text-warning"></i>@endif
                </h2>
                @if ($subtitle)
                    <p class="text-muted-2 mb-0 small fw-semibold">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($viewAll)
                <a href="{{ $viewAll }}" class="explore-link">Explore more <i class="bi bi-chevron-right small"></i></a>
            @endif
        </div>
        <div class="slider-viewport">
            <button type="button" class="slider-btn slider-btn-prev" data-slider-prev aria-controls="{{ $id }}" aria-label="Previous products"><i class="bi bi-chevron-left"></i></button>
            <div class="slider-track" id="{{ $id }}" data-slider-track tabindex="0" role="region" aria-label="{{ $title }} products">
                @foreach ($products as $product)
                    <div class="slider-item">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
            <button type="button" class="slider-btn slider-btn-next" data-slider-next aria-controls="{{ $id }}" aria-label="Next products"><i class="bi bi-chevron-right"></i></button>
        </div>
    </section>
@endif
