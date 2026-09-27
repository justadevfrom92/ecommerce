<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
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

        $customer = $order->user?->stripe_customer_id;

        $response = $this->client()->asForm()->post(self::API.'/checkout/sessions', [
            'mode' => 'payment',
            ...($customer ? ['customer' => $customer, 'saved_payment_method_options' => ['payment_method_save' => 'enabled']] : ['customer_email' => $order->email]),
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

    /*
    |--------------------------------------------------------------------------
    | Saved payment methods (cards etc. are stored by Stripe, never by us)
    |--------------------------------------------------------------------------
    */

    /** Returns the user's Stripe customer id, creating the customer on first use. */
    public function customerFor(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $data = $this->call('post', '/customers', [
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->forceFill(['stripe_customer_id' => $data['id']])->save();

        return $data['id'];
    }

    /** Stripe-hosted page where the customer adds a payment method. */
    public function createSetupSession(User $user, string $successUrl, string $cancelUrl): string
    {
        $data = $this->call('post', '/checkout/sessions', [
            'mode' => 'setup',
            'customer' => $this->customerFor($user),
            'currency' => strtolower(setting('currency', 'usd')),
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return $data['url'];
    }

    /**
     * @return array<int, array{id: string, type: string, label: string, detail: string, default: bool}>
     */
    public function paymentMethods(User $user): array
    {
        if (! $user->stripe_customer_id) {
            return [];
        }

        $customer = $this->call('get', '/customers/'.urlencode($user->stripe_customer_id));
        $default = $customer['invoice_settings']['default_payment_method'] ?? null;
        $methods = $this->call('get', '/customers/'.urlencode($user->stripe_customer_id).'/payment_methods', ['limit' => 20])['data'] ?? [];

        return array_map(function (array $pm) use ($default) {
            $type = $pm['type'] ?? 'card';
            $details = $pm[$type] ?? [];

            [$label, $detail] = match ($type) {
                'card' => [ucfirst($details['brand'] ?? 'Card').' •••• '.($details['last4'] ?? ''), 'Expires '.str_pad((string) ($details['exp_month'] ?? ''), 2, '0', STR_PAD_LEFT).'/'.($details['exp_year'] ?? '')],
                'us_bank_account', 'sepa_debit', 'bacs_debit', 'au_becs_debit' => [($details['bank_name'] ?? 'Bank account').' •••• '.($details['last4'] ?? ''), 'Bank account'],
                'paypal' => ['PayPal', $details['payer_email'] ?? ''],
                'link' => ['Link', $details['email'] ?? ''],
                default => [ucwords(str_replace('_', ' ', $type)), ''],
            };

            return ['id' => $pm['id'], 'type' => $type, 'label' => $label, 'detail' => $detail, 'default' => $pm['id'] === $default];
        }, $methods);
    }

    public function setDefaultPaymentMethod(User $user, string $paymentMethodId): void
    {
        $this->assertOwns($user, $paymentMethodId);
        $this->call('post', '/customers/'.urlencode($user->stripe_customer_id), [
            'invoice_settings' => ['default_payment_method' => $paymentMethodId],
        ]);
    }

    public function detachPaymentMethod(User $user, string $paymentMethodId): void
    {
        $this->assertOwns($user, $paymentMethodId);
        $this->call('post', '/payment_methods/'.urlencode($paymentMethodId).'/detach');
    }

    /** Never act on a payment method that isn't attached to this user's customer. */
    private function assertOwns(User $user, string $paymentMethodId): void
    {
        $pm = $this->call('get', '/payment_methods/'.urlencode($paymentMethodId));

        if (! $user->stripe_customer_id || ($pm['customer'] ?? null) !== $user->stripe_customer_id) {
            throw new RuntimeException('That payment method does not belong to this account.');
        }
    }

    private function call(string $method, string $path, array $data = []): array
    {
        $request = $this->client();
        $response = $method === 'get'
            ? $request->get(self::API.$path, $data)
            : $request->asForm()->post(self::API.$path, $data);

        if ($response->failed()) {
            throw new RuntimeException('Stripe error: '.$response->json('error.message', $response->body()));
        }

        return $response->json() ?? [];
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
