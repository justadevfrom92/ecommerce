<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'source', 'source_id', 'max_items', 'sort_order', 'is_active'])]
class HomeSlider extends Model
{
    public const SOURCES = [
        'featured' => 'Featured products',
        'new' => 'Newest products',
        'sale' => 'On sale',
        'department' => 'A department',
        'category' => 'A category',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }

    /** @return Collection<int, Product> */
    public function products(): Collection
    {
        $query = Product::query()->active()->with('category');

        match ($this->source) {
            'featured' => $query->where('is_featured', true)->latest(),
            'sale' => $query->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price')->latest(),
            'department' => $query->whereHas('category', fn (Builder $q) => $q->where('department_id', $this->source_id))->latest(),
            'category' => $query->where('category_id', $this->source_id)->latest(),
            default => $query->latest(),
        };

        return $query->limit($this->max_items)->get();
    }

    public function viewAllUrl(): ?string
    {
        return match ($this->source) {
            'department' => ($d = Department::find($this->source_id)) ? route('departments.show', $d) : null,
            'category' => ($c = Category::find($this->source_id)) ? route('categories.show', $c) : null,
            'sale' => route('search', ['on_sale' => 1]),
            default => route('search'),
        };
    }
}
