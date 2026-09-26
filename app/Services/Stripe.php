<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal Stripe Checkout client over the REST API.
 * https://docs.stripe.com/api/checkout/sessions
 */
class Stripe
{
    private const API = 'https://api.stripe.com/v1';

    public function enabled(): bool
    {
        return filled(config('services.stripe.secret'));
    }

    /** @return array{id: string, url: string} */
    public function createCheckoutSession(Order $order): array
    {
        $order->loadMissing('items');
        $currency = strtolower($order->currency);

        $lines = $order->items->map(fn ($item) => [
            'quantity' => $item->quantity,
            'price_data' => [
                'currency' => $currency,
                'unit_amount' => $this->cents($item->price),
                'product_data' => ['name' => $item->name],
            ],
        ])->all();

        foreach (['shipping' => 'Shipping', 'tax' => 'Tax'] as $field => $label) {
            if ((float) $order->{$field} > 0) {
                $lines[] = [
                    'quantity' => 1,
                    'price_data' => ['currency' => $currency, 'unit_amount' => $this->cents($order->{$field}), 'product_data' => ['name' => $label]],
                ];
            }
        }

        $response = $this->client()->asForm()->post(self::API.'/checkout/sessions', [
            'mode' => 'payment',
            'customer_email' => $order->email,
            'client_reference_id' => (string) $order->id,
            'metadata' => ['order_id' => $order->id, 'order_number' => $order->number],
            'line_items' => $lines,
            'success_url' => route('checkout.success', $order).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', $order),
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Stripe error: '.$response->json('error.message', $response->body()));
        }

        return ['id' => $response->json('id'), 'url' => $response->json('url')];
    }

    public function retrieveCheckoutSession(string $id): array
    {
        $response = $this->client()->get(self::API.'/checkout/sessions/'.urlencode($id));

        if ($response->failed()) {
            throw new RuntimeException('Stripe error: '.$response->json('error.message', $response->body()));
        }

        return $response->json();
    }

    /**
     * Verifies the Stripe-Signature header (https://docs.stripe.com/webhooks#verify-manually).
     */
    public function verifyWebhook(string $payload, ?string $header, int $tolerance = 300): bool
    {
        $secret = config('services.stripe.webhook_secret');

        if (! $secret || ! $header) {
            return false;
        }

        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);
            if ($key === 't') {
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if (! $timestamp || $signatures === [] || abs(time() - $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, (string) $signature)) {
                return true;
            }
        }

        return false;
    }

    private function client(): PendingRequest
    {
        return Http::withToken(config('services.stripe.secret'))->acceptJson()->timeout(20)->retry(2, 200, throw: false);
    }

    private function cents(float|string $amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
