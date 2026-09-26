<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Applies the shared search / sort / per-page behaviour to a query so every
 * admin and account table works the same way. Pair with <x-data-table>.
 */
class DataTable
{
    /**
     * @param  array<int, string>  $searchable  Columns matched by the search box.
     * @param  array<int, string>  $sortable  Columns the user may sort by.
     */
    public static function paginate(
        Builder $query,
        Request $request,
        array $searchable = [],
        array $sortable = [],
        string $defaultSort = 'id',
        string $defaultDirection = 'desc',
    ): LengthAwarePaginator {
        $search = trim((string) $request->query('search', ''));

        if ($search !== '' && $searchable !== []) {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $search).'%';

            $query->where(function (Builder $q) use ($searchable, $like) {
                foreach ($searchable as $column) {
                    if (str_contains($column, '.')) {
                        [$relation, $field] = explode('.', $column, 2);
                        $q->orWhereHas($relation, fn (Builder $r) => $r->where($field, 'like', $like));
                    } else {
                        $q->orWhere($q->qualifyColumn($column), 'like', $like);
                    }
                }
            });
        }

        $sort = $request->query('sort');
        $sort = in_array($sort, $sortable, true) ? $sort : $defaultSort;
        $direction = $request->query('direction') === 'asc' ? 'asc' : ($request->query('direction') === 'desc' ? 'desc' : $defaultDirection);

        $query->orderBy($query->qualifyColumn($sort), $direction);

        if ($sort !== 'id') {
            $query->orderBy($query->qualifyColumn('id'), 'desc');
        }

        $options = config('store.per_page_options');
        $perPage = (int) $request->query('per_page', $options[1] ?? 20);
        $perPage = in_array($perPage, $options, true) ? $perPage : ($options[1] ?? 20);

        return $query->paginate($perPage)->withQueryString();
    }
}
