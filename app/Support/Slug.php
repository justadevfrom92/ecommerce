<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slug
{
    /** A slug for $source that's unique in the model's table (ignoring $ignoreId). */
    public static function unique(string $modelClass, string $source, ?int $ignoreId = null): string
    {
        /** @var Model $model */
        $model = new $modelClass;
        $base = Str::slug($source) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while ($model->newQuery()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
