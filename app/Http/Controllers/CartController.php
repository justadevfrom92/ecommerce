<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private Cart $cart) {}

    public function index(): View
    {
        $lines = $this->cart->lines();

        $suggestions = Product::active()->with('category')
            ->where('is_featured', true)
            ->whereNotIn('id', $lines->pluck('product.id'))
            ->inRandomOrder()->limit(12)->get();

        return view('store.cart', [
            'lines' => $lines,
            'totals' => $this->cart->totals($lines),
            'suggestions' => $suggestions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);

        if (! $product->inStock()) {
            return back()->with('error', "Sorry, {$product->name} is out of stock.");
        }

        $this->cart->add($product, $data['quantity'] ?? 1);

        return back()->with('status', "{$product->name} added to your cart.");
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']]);

        if ($data['quantity'] === 0) {
            $this->cart->remove($product->id);
        } else {
            $this->cart->update($product, $data['quantity']);

            if ($data['quantity'] > $product->stock) {
                return back()->with('error', "Only {$product->stock} of {$product->name} available.");
            }
        }

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->cart->remove($product->id);

        return back()->with('status', "{$product->name} removed from your cart.");
    }
}
