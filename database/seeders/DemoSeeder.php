<?php

namespace Database\Seeders;

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
    }
}
