{{-- Admin/store page heading: breadcrumb, big title, optional subtitle and actions on the right. --}}
@props(['title', 'crumbs' => [], 'subtitle' => null])
<div {{ $attributes->class('admin-page-head') }}>
    @if ($crumbs)
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                @foreach ($crumbs as $label => $url)
                    @if ($url)
                        <li class="breadcrumb-item"><a href="{{ $url }}">{{ $label }}</a></li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
        <div>
            <h1 class="page-title">{{ $title }}</h1>
            @if ($subtitle)<p class="admin-subtitle mt-1">{{ $subtitle }}</p>@endif
        </div>
        @isset($actions)
            <div class="d-flex flex-wrap gap-2 align-items-center">{{ $actions }}</div>
        @endisset
    </div>
</div>
