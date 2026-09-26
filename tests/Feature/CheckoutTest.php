<?php

namespace Tests\Feature;

use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private array $address = [
        'shipping_name' => 'Jamie Doe',
        'shipping_line1' => '1 Main St',
        'shipping_city' => 'Springfield',
        'shipping_postal_code' => '12345',
        'shipping_country' => 'us',
    ];

    public function test_checkout_requires_login(): void
    {
        $this->get('/checkout')->assertRedirect(route('login'));
    }

    public function test_order_placed_without_stripe_is_pending_and_stock_reserved(): void
    {
        config(['services.stripe.secret' => null]);
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 30, 'stock' => 5]);

        $this->actingAs($user)->post('/cart', ['product_id' => $product->id, 'quantity' => 2]);
        $this->actingAs($user)->get('/checkout')->assertOk()->assertSee('Place order');

        $response = $this->actingAs($user)->post('/checkout', $this->address);

        $order = Order::first();
        $response->assertRedirect(route('account.orders.show', $order));
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame('US', $order->shipping_country);
        $this->assertEquals(60, $order->subtotal);
        $this->assertEquals(65, $order->total); // + $5 shipping under $75
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertEmpty(session('cart'));
    }

    public function test_checkout_rejects_when_stock_ran_out(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user)->post('/cart', ['product_id' => $product->id, 'quantity' => 3]);
        $product->update(['stock' => 1]);

        $this->actingAs($user)->post('/checkout', $this->address)->assertSessionHasErrors('cart');
        $this->assertSame(0, Order::count());
    }

    public function test_stripe_checkout_redirects_and_success_marks_paid(): void
    {
        Event::fake([OrderPaid::class]);
        config(['services.stripe.secret' => 'sk_test_x']);
        Http::fake([
            'api.stripe.com/v1/checkout/sessions' => Http::response(['id' => 'cs_test_1', 'url' => 'https://checkout.stripe.com/pay/cs_test_1']),
            'api.stripe.com/v1/checkout/sessions/cs_test_1' => Http::response(['id' => 'cs_test_1', 'payment_status' => 'paid', 'payment_intent' => 'pi_1']),
        ]);

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100, 'stock' => 5]);
        $this->actingAs($user)->post('/cart', ['product_id' => $product->id]);

        $this->actingAs($user)->post('/checkout', $this->address)
            ->assertRedirect('https://checkout.stripe.com/pay/cs_test_1');

        Http::assertSent(fn ($r) => $r->url() === 'https://api.stripe.com/v1/checkout/sessions'
            && $r['line_items'][0]['price_data']['unit_amount'] === 10000
            && $r['mode'] === 'payment');

        $order = Order::first();
        $this->assertSame('cs_test_1', $order->stripe_session_id);

        $this->actingAs($user)->get(route('checkout.success', $order).'?session_id=cs_test_1')
            ->assertRedirect(route('account.orders.show', $order));

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('pi_1', $order->fresh()->stripe_payment_intent);
        Event::assertDispatchedTimes(OrderPaid::class, 1);
    }

    public function test_webhook_requires_valid_signature_and_marks_paid_once(): void
    {
        Event::fake([OrderPaid::class]);
        config(['services.stripe.webhook_secret' => 'whsec_test']);
        $order = Order::factory()->create(['status' => 'pending_payment', 'stripe_session_id' => 'cs_live_9']);

        $payload = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => [
            'id' => 'cs_live_9', 'payment_status' => 'paid', 'payment_intent' => 'pi_9',
        ]]]);

        $this->call('POST', '/webhooks/stripe', [], [], [], ['HTTP_STRIPE_SIGNATURE' => 't='.time().',v1=bad', 'CONTENT_TYPE' => 'application/json'], $payload)
            ->assertStatus(400);

        $t = time();
        $sig = 't='.$t.',v1='.hash_hmac('sha256', $t.'.'.$payload, 'whsec_test');

        foreach ([1, 2] as $_) {
            $this->call('POST', '/webhooks/stripe', [], [], [], ['HTTP_STRIPE_SIGNATURE' => $sig, 'CONTENT_TYPE' => 'application/json'], $payload)
                ->assertOk();
        }

        $this->assertSame('paid', $order->fresh()->status);
        Event::assertDispatchedTimes(OrderPaid::class, 1);
    }

    public function test_customers_only_see_their_own_orders(): void
    {
        $mine = Order::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($mine->user)->get('/account/orders')->assertOk()->assertSee($mine->number);
        $this->actingAs($mine->user)->get('/account/orders/'.$mine->number)->assertOk();
        $this->actingAs($other)->get('/account/orders/'.$mine->number)->assertNotFound();
        $this->actingAs($other)->post(route('checkout.pay', $mine))->assertForbidden();
    }

    public function test_cancelling_returns_stock(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        $order = Order::factory()->create(['status' => 'paid']);
        $order->items()->create(['product_id' => $product->id, 'name' => 'x', 'sku' => 'x', 'price' => 1, 'quantity' => 4, 'line_total' => 4]);

        $order->transitionTo('cancelled');
        $this->assertSame(14, $product->fresh()->stock);

        $order->transitionTo('refunded'); // already restocked — no double count
        $this->assertSame(14, $product->fresh()->stock);

        $order->transitionTo('processing');
        $this->assertSame(10, $product->fresh()->stock);
    }
}
