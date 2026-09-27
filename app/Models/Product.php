<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['category_id', 'name', 'slug', 'sku', 'description', 'price', 'compare_at_price', 'stock', 'image_path', 'is_active', 'is_featured'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** Sliders only ever show products a shopper can actually buy. */
    public function scopeInStock(Builder $query): void
    {
        $query->where('stock', '>', 0);
    }

    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term).'%';

        $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    public function imageUrl(): string
    {
        return $this->image_path
            ? Storage::disk('public')->url($this->image_path)
            : asset('images/placeholder.svg');
    }

    public function isOnSale(): bool
    {
        return $this->compare_at_price !== null && (float) $this->compare_at_price > (float) $this->price;
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
    }
}
