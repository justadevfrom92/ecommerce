<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionSeeder::class);

        // First admin. Log in with these, then change the password under My account.
        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@mystore.test')],
            ['name' => 'Store Admin', 'password' => env('ADMIN_PASSWORD', 'password'), 'email_verified_at' => now()],
        );
        $admin->roles()->syncWithoutDetaching(Role::where('slug', 'super-admin')->pluck('id'));

        if (app()->environment(['local', 'testing']) || env('SEED_DEMO_DATA', false)) {
            $this->call(DemoSeeder::class);
        }

        $this->call([SettingsSeeder::class, EmailTemplateSeeder::class]);
    }
}
