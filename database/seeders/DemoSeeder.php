<?php

namespace Database\Seeders;

use App\Models\InboundMessage;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Demo data so the store and admin tables have something to show. Local/testing only. */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CatalogSeeder::class);

        if (User::count() < 10) {
            $customer = Role::where('slug', 'customer')->first();
            User::factory()->count(75)->create()->each(fn (User $u) => $u->roles()->attach($customer));

            $manager = User::factory()->create(['name' => 'Morgan Manager', 'email' => 'manager@mystore.test']);
            $manager->roles()->attach(Role::where('slug', 'store-manager')->first());
        }

        if (Order::doesntExist()) {
            $customers = User::whereHas('roles', fn ($q) => $q->where('slug', 'customer'))->get();

            foreach (range(1, 160) as $i) {
                $customer = $customers->random();
                Order::factory()->withItems()->create([
                    'user_id' => $customer->id,
                    'email' => $customer->email,
                    'shipping_name' => $customer->name,
                ]);
            }
        }

        if (InboundMessage::doesntExist()) {
            foreach (range(1, 14) as $i) {
                InboundMessage::create([
                    'source' => fake()->randomElement(['email', 'contact']),
                    'from_email' => fake()->safeEmail(),
                    'from_name' => fake()->name(),
                    'subject' => fake()->randomElement(['Where is my order?', 'Question about sizing', 'Return request', 'Do you ship to Canada?', 'Wholesale enquiry']),
                    'body' => fake()->paragraphs(2, true),
                    'read_at' => fake()->boolean(50) ? now() : null,
                    'created_at' => fake()->dateTimeBetween('-3 weeks'),
                ]);
            }
        }
    }
}
