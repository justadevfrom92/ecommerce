<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\Cart;
use App\Services\Stripe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(private Cart $cart, private Stripe $stripe) {}

    public function create(Request $request): View|RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $lastOrder = $request->user()->orders()->latest()->first();

        return view('store.checkout', [
            'lines' => $lines,
            'totals' => $this->cart->totals($lines),
            'user' => $request->user(),
            'last' => $lastOrder,
            'stripeEnabled' => $this->stripe->enabled(),
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['nullable', 'string', 'max:40'],
            'shipping_line1' => ['required', 'string', 'max:255'],
            'shipping_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_state' => ['nullable', 'string', 'max:120'],
            'shipping_postal_code' => ['required', 'string', 'max:20'],
            'shipping_country' => ['required', 'string', 'size:2'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $raw = $this->cart->raw();

        if ($raw === []) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $order = DB::transaction(function () use ($data, $raw, $request) {
            // Lock the rows so two shoppers can't buy the last unit at once.
            $products = Product::active()->whereIn('id', array_keys($raw))->lockForUpdate()->get()->keyBy('id');

            foreach ($raw as $productId => $qty) {
                $product = $products->get($productId);
                if (! $product || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'cart' => ($product?->name ?? 'An item').' no longer has enough stock. Please review your cart.',
                    ]);
                }
            }

            $lines = $products->map(fn (Product $p) => (object) [
                'product' => $p, 'quantity' => $raw[$p->id], 'total' => round((float) $p->price * $raw[$p->id], 2),
            ]);
            $totals = $this->cart->totals($lines);

            $order = Order::create([
                ...$data,
                'shipping_country' => strtoupper($data['shipping_country']),
                'number' => Order::generateNumber(),
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'status' => 'pending_payment',
                'currency' => setting('currency', 'usd'),
                ...$totals,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line->product->id,
                    'name' => $line->product->name,
                    'sku' => $line->product->sku,
                    'price' => $line->product->price,
                    'quantity' => $line->quantity,
                    'line_total' => $line->total,
                ]);
                $line->product->decrement('stock', $line->quantity);
            }

            return $order;
        });

        $this->cart->clear();

        return $this->pay($order);
    }

    /** Sends the customer to Stripe Checkout (also used for "Pay now" on an unpaid order). */
    public function pay(Order $order): Response
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 403);

        if (! $order->isAwaitingPayment()) {
            return redirect()->route('account.orders.show', $order);
        }

        if (! $this->stripe->enabled()) {
            return redirect()->route('account.orders.show', $order)
                ->with('status', "Order {$order->number} placed. Online payment isn't set up yet, so we'll contact you about payment.");
        }

        try {
            $session = $this->stripe->createCheckoutSession($order);
        } catch (Throwable $e) {
            Log::error('Stripe checkout session failed', ['order' => $order->number, 'error' => $e->getMessage()]);

            return redirect()->route('account.orders.show', $order)
                ->with('error', 'We could not reach the payment provider. Your order is saved; please try "Pay now" again shortly.');
        }

        $order->update(['stripe_session_id' => $session['id']]);

        return redirect()->away($session['url'], 303);
    }

    public function success(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === $request->user()->id, 403);

        $sessionId = (string) $request->query('session_id');

        if ($order->isAwaitingPayment() && $sessionId !== '' && $sessionId === $order->stripe_session_id && $this->stripe->enabled()) {
            try {
                $session = $this->stripe->retrieveCheckoutSession($sessionId);
                if (($session['payment_status'] ?? null) === 'paid') {
                    $order->markPaid($session['payment_intent'] ?? null);
                }
            } catch (Throwable $e) {
                // The webhook will still mark it paid.
                Log::warning('Stripe session lookup failed', ['order' => $order->number, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('account.orders.show', $order)->with('status', 'Thank you! Your order has been placed.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === $request->user()->id, 403);

        return redirect()->route('account.orders.show', $order)
            ->with('error', 'Payment was cancelled. Your order is saved — use "Pay now" when you\'re ready.');
    }
}
