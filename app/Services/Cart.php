<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Session-backed shopping cart: product_id => quantity. Works for guests and
 * survives login (Laravel keeps session data when it regenerates the ID).
 */
class Cart
{
    private const KEY = 'cart';

    public function __construct(private Session $session) {}

    /** @return array<int, int> */
    public function raw(): array
    {
        return $this->session->get(self::KEY, []);
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $items = $this->raw();
        $items[$product->id] = min(($items[$product->id] ?? 0) + $quantity, max(0, $product->stock));
        $this->save($items);
    }

    public function update(Product $product, int $quantity): void
    {
        $items = $this->raw();
        $items[$product->id] = min($quantity, max(0, $product->stock));
        $this->save($items);
    }

    public function remove(int $productId): void
    {
        $items = $this->raw();
        unset($items[$productId]);
        $this->save($items);
    }

    public function clear(): void
    {
        $this->session->forget(self::KEY);
    }

    /**
     * Cart lines with current product data. Products that were deleted or
     * deactivated since being added are dropped.
     *
     * @return Collection<int, object{product: Product, quantity: int, total: float}>
     */
    public function lines(): Collection
    {
        $raw = $this->raw();

        if ($raw === []) {
            return collect();
        }

        $products = Product::query()->active()->with('category')->whereIn('id', array_keys($raw))->get()->keyBy('id');

        if ($products->count() !== count($raw)) {
            $this->save(array_intersect_key($raw, $products->all()));
        }

        return $products->map(fn (Product $product) => (object) [
            'product' => $product,
            'quantity' => $raw[$product->id],
            'total' => round((float) $product->price * $raw[$product->id], 2),
        ])->values();
    }

    /** @return array{subtotal: float, shipping: float, tax: float, total: float} */
    public function totals(?Collection $lines = null): array
    {
        $lines ??= $this->lines();
        $subtotal = round($lines->sum('total'), 2);

        $freeOver = (float) setting('free_shipping_over');
        $shipping = $subtotal <= 0 || ($freeOver > 0 && $subtotal >= $freeOver) ? 0.0 : (float) setting('shipping_flat_rate');
        $tax = round($subtotal * ((float) setting('tax_rate') / 100), 2);

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => round($subtotal + $shipping + $tax, 2),
        ];
    }

    /** @param  array<int, int>  $items */
    private function save(array $items): void
    {
        $this->session->put(self::KEY, array_filter($items, fn (int $qty) => $qty > 0));
    }
}
