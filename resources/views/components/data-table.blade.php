{{--
    The one table used everywhere (admin users, products, orders, emails… and account orders).

    <x-data-table :rows="$users" search-placeholder="Search users…" :columns="[
        'name'       => ['label' => 'Name', 'sortable' => true],
        'email'      => ['label' => 'Email', 'sortable' => true],
        'actions'    => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:toolbar> <a class="btn btn-primary btn-sm" href="…">Add user</a> </x-slot:toolbar>
        <x-slot:filters> …extra <select>s, they submit with the search form… </x-slot:filters>
        @foreach ($users as $user) <tr>…</tr> @endforeach
    </x-data-table>

    Pair with App\Support\DataTable::paginate() in the controller.
--}}
@props([
    'rows',
    'columns' => [],
    'searchPlaceholder' => 'Search…',
    'searchable' => true,
    'empty' => 'No records found.',
    'label' => 'results',
])
@php
    $currentSort = request('sort');
    $currentDirection = request('direction') === 'asc' ? 'asc' : 'desc';
    $perPageOptions = config('store.per_page_options');
@endphp
<div {{ $attributes->class('card data-table shadow-sm') }}>
    <div class="card-header bg-body d-flex flex-column flex-lg-row gap-2 align-items-lg-center justify-content-between py-3">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center data-table-filters" role="search">
            @if ($currentSort)
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
            @endif
            @if ($searchable)
                <div class="input-group input-group-sm data-table-search">
                    <span class="input-group-text bg-body"><i class="bi bi-search"></i></span>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ $searchPlaceholder }}" aria-label="{{ $searchPlaceholder }}">
                </div>
            @endif
            {{ $filters ?? '' }}
            <select name="per_page" class="form-select form-select-sm w-auto" aria-label="Rows per page" data-auto-submit>
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}" @selected($rows->perPage() === $option)>{{ $option }} / page</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">Apply</button>
            @if (collect(request()->query())->except(['page'])->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
                <a href="{{ url()->current() }}" class="btn btn-sm btn-link text-body-secondary">Reset</a>
            @endif
        </form>
        @isset($toolbar)
            <div class="d-flex gap-2 flex-shrink-0">{{ $toolbar }}</div>
        @endisset
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    @foreach ($columns as $key => $column)
                        @php($column = is_array($column) ? $column : ['label' => $column])
                        <th scope="col" class="{{ $column['class'] ?? '' }} text-nowrap small text-uppercase text-body-secondary"
                            @if (! empty($column['sortable'])) aria-sort="{{ $currentSort === $key ? ($currentDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}" @endif>
                            @if (! empty($column['sortable']))
                                <a href="{{ sort_url($key) }}" class="sort-link text-reset text-decoration-none">
                                    {{ $column['label'] }}
                                    @if ($currentSort === $key)
                                        <i class="bi bi-caret-{{ $currentDirection === 'asc' ? 'up' : 'down' }}-fill"></i>
                                    @else
                                        <i class="bi bi-chevron-expand opacity-50"></i>
                                    @endif
                                </a>
                            @else
                                {{ $column['label'] }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @if ($rows->isEmpty())
                    <tr>
                        <td colspan="{{ count($columns) }}" class="text-center text-body-secondary py-5">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>{{ $empty }}
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-body py-3">
        <x-pagination :paginator="$rows" :label="$label" />
    </div>
</div>
