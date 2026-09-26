<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('money')) {
    function money(float|int|string|null $amount): string
    {
        return setting('currency_symbol', '$').number_format((float) $amount, 2);
    }
}

if (! function_exists('sort_url')) {
    /** URL that sorts the current table by $column, toggling direction when already sorted by it. */
    function sort_url(string $column): string
    {
        $request = request();
        $active = $request->query('sort') === $column;
        $direction = $active && $request->query('direction') === 'asc' ? 'desc' : 'asc';

        return $request->fullUrlWithQuery(['sort' => $column, 'direction' => $direction, 'page' => null]);
    }
}
