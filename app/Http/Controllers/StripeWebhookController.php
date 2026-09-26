<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Stripe;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, Stripe $stripe): Response
    {
        $payload = $request->getContent();

        if (! $stripe->verifyWebhook($payload, $request->header('Stripe-Signature'))) {
            return response('Invalid signature', 400);
        }

        $event = json_decode($payload, true);
        $session = $event['data']['object'] ?? [];
        $order = isset($session['id']) ? Order::where('stripe_session_id', $session['id'])->first() : null;

        if (! $order) {
            return response('Ignored', 200);
        }

        match ($event['type'] ?? null) {
            'checkout.session.completed', 'checkout.session.async_payment_succeeded' => ($session['payment_status'] ?? null) === 'paid'
                ? $order->markPaid($session['payment_intent'] ?? null)
                : null,
            default => null,
        };

        return response('OK', 200);
    }
}
