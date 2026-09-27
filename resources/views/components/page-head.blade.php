{{-- Admin/store page heading: big title, optional subtitle and actions on the right. --}}
@props(['title', 'subtitle' => null])
<div {{ $attributes->class('admin-page-head') }}>
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
