<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement(array_keys(Order::STATUSES));
        $created = fake()->dateTimeBetween('-6 months');

        return [
            'number' => Order::generateNumber(),
            'user_id' => User::factory(),
            'status' => $status,
            'email' => fake()->safeEmail(),
            'shipping_name' => fake()->name(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_line1' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_state' => fake()->stateAbbr(),
            'shipping_postal_code' => fake()->postcode(),
            'shipping_country' => 'US',
            'subtotal' => 0,
            'shipping' => 0,
            'tax' => 0,
            'total' => 0,
            'currency' => 'usd',
            'paid_at' => $status === 'pending_payment' ? null : $created,
            'created_at' => $created,
            'updated_at' => $created,
        ];
    }

    /** Adds 1–4 real line items and sets the totals to match. */
    public function withItems(): static
    {
        return $this->afterCreating(function (Order $order) {
            $products = Product::inRandomOrder()->limit(fake()->numberBetween(1, 4))->get();

            if ($products->isEmpty()) {
                $products = Product::factory()->count(2)->create();
            }

            foreach ($products as $product) {
                $qty = fake()->numberBetween(1, 3);
                $order->items()->create([
                    'product_id' => $product->id, 'name' => $product->name, 'sku' => $product->sku,
                    'price' => $product->price, 'quantity' => $qty, 'line_total' => round($product->price * $qty, 2),
                ]);
            }

            $subtotal = round($order->items()->sum('line_total'), 2);
            $shipping = $subtotal >= 75 ? 0 : 5;
            $order->update(['subtotal' => $subtotal, 'shipping' => $shipping, 'total' => $subtotal + $shipping]);
        });
    }
}
