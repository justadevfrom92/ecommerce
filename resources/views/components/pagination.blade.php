{{--
    Shared pagination: "Showing 21 to 40 of 385 results" + 1 2 3 4 5 6 7 … 20.
    Usage: <x-pagination :paginator="$products" />
--}}
@props(['paginator', 'label' => 'results'])
@php($window = \App\Support\PageWindow::make($paginator->currentPage(), $paginator->lastPage()))
<div {{ $attributes->class('d-flex flex-column flex-md-row align-items-center justify-content-between gap-2') }}>
    <p class="small text-body-secondary mb-0" aria-live="polite">
        @if ($paginator->total() > 0)
            Showing <span class="fw-semibold">{{ number_format($paginator->firstItem()) }}</span>
            to <span class="fw-semibold">{{ number_format($paginator->lastItem()) }}</span>
            of <span class="fw-semibold">{{ number_format($paginator->total()) }}</span> {{ $label }}
        @else
            No {{ $label }}
        @endif
    </p>

    @if ($paginator->hasPages())
        <nav aria-label="Pagination">
            <ul class="pagination pagination-sm mb-0 flex-wrap">
                <li class="page-item @if ($paginator->onFirstPage()) disabled @endif">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() ?? '#' }}" rel="prev" aria-label="Previous page">&lsaquo;</a>
                </li>
                @foreach ($window as $page)
                    @if ($page === null)
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    @elseif ($page === $paginator->currentPage())
                        <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url($page) }}">{{ $page }}</a></li>
                    @endif
                @endforeach
                <li class="page-item @if (! $paginator->hasMorePages()) disabled @endif">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() ?? '#' }}" rel="next" aria-label="Next page">&rsaquo;</a>
                </li>
            </ul>
        </nav>
    @endif
</div>
