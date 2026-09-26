<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public const CACHE_KEY = 'settings.all';

    /** Defaults used until an admin saves a value in /admin/settings. */
    public const DEFAULTS = [
        'store_name' => 'MyStore',
        'store_email' => 'hello@mystore.test',
        'currency' => 'usd',
        'currency_symbol' => '$',
        'shipping_flat_rate' => '5.00',
        'free_shipping_over' => '75.00',
        'tax_rate' => '0',
    ];

    /** @return array<string, string|null> */
    public static function allCached(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
        } catch (Throwable) {
            // Table not migrated yet (fresh install) — fall back to defaults.
            return [];
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::allCached()[$key] ?? self::DEFAULTS[$key] ?? $default;
    }

    /** @param  array<string, mixed>  $values */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
