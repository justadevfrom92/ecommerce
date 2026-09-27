{{--
    The one table used everywhere (admin users, products, orders, emails… and account orders).

    <x-data-table :rows="$users" search-placeholder="Search users…" :columns="[
        'name'    => ['label' => 'Name', 'sortable' => true],
        'actions' => ['label' => '', 'class' => 'text-end'],
    ]">
        <x-slot:tabs> <a href="…" class="active">All <span class="count">(120)</span></a> … </x-slot:tabs>
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
    'keep' => [],   // query keys to carry through a search (e.g. the active status tab)
])
@php
    $currentSort = request('sort');
    $currentDirection = request('direction') === 'asc' ? 'asc' : 'desc';
    $perPageOptions = config('store.per_page_options');
@endphp
<div {{ $attributes->class('data-table') }}>
    @isset($tabs)
        <div class="dt-tabs">{{ $tabs }}</div>
    @endisset

    <div class="dt-toolbar">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center flex-grow-1" role="search">
            @foreach (request()->only($keep) as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            @if ($currentSort)
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
            @endif
            @if ($searchable)
                <div class="search-pill dt-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ $searchPlaceholder }}" aria-label="{{ $searchPlaceholder }}" style="border-radius: var(--ms-radius); height: 2.35rem">
                </div>
            @endif
            <div class="dt-filters">
                {{ $filters ?? '' }}
                <select name="per_page" class="form-select form-select-sm w-auto" aria-label="Rows per page" data-auto-submit>
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($rows->perPage() === $option)>{{ $option }} / page</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-soft btn-sm">Apply</button>
                @if (collect(request()->query())->except(['page'])->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-link text-muted-2 fw-bold">Reset</a>
                @endif
            </div>
        </form>
        @isset($toolbar)
            <div class="d-flex gap-2 flex-shrink-0 ms-auto">{{ $toolbar }}</div>
        @endisset
    </div>

    <div class="dt-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        @foreach ($columns as $key => $column)
                            @php($column = is_array($column) ? $column : ['label' => $column])
                            <th scope="col" class="{{ $column['class'] ?? '' }}"
                                @if (! empty($column['sortable'])) aria-sort="{{ $currentSort === $key ? ($currentDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}" @endif>
                                @if (! empty($column['sortable']))
                                    <a href="{{ sort_url($key) }}" class="sort-link">
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
                            <td colspan="{{ count($columns) }}" class="dt-empty">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>{{ $empty }}
                            </td>
                        </tr>
                    @else
                        {{ $slot }}
                    @endif
                </tbody>
            </table>
        </div>
        <div class="dt-footer">
            <x-pagination :paginator="$rows" :label="$label" />
        </div>
    </div>
</div>
