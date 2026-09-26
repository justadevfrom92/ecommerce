<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/** Syncs permissions from config/store.php and creates the starter roles. Safe to re-run. */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('store.permissions') as $group => $permissions) {
            foreach ($permissions as $slug => $name) {
                Permission::updateOrCreate(['slug' => $slug], ['name' => $name, 'group' => $group]);
            }
        }

        Role::updateOrCreate(['slug' => 'super-admin'], [
            'name' => 'Super Admin',
            'description' => 'Full access to everything.',
            'is_super' => true,
        ]);

        Role::updateOrCreate(['slug' => 'customer'], [
            'name' => 'Customer',
            'description' => 'Default role for people who sign up. No admin access.',
        ]);

        $starter = [
            'store-manager' => ['Store Manager', 'Runs the catalog and orders.', [
                'admin.access', 'dashboard.view', 'products.view', 'products.manage', 'departments.manage',
                'categories.manage', 'orders.view', 'orders.manage', 'users.view',
            ]],
            'support' => ['Customer Support', 'Handles orders and the inbox.', [
                'admin.access', 'orders.view', 'users.view', 'emails.inbox', 'emails.log',
            ]],
            'marketing' => ['Marketing', 'Newsletters, templates and homepage sliders.', [
                'admin.access', 'dashboard.view', 'products.view', 'emails.templates', 'emails.subscribers',
                'emails.campaigns', 'settings.manage',
            ]],
        ];

        foreach ($starter as $slug => [$name, $description, $permissions]) {
            $role = Role::firstOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description]);

            // Only fill a brand-new role; never overwrite what an admin has edited.
            if ($role->wasRecentlyCreated) {
                $role->permissions()->sync(Permission::whereIn('slug', $permissions)->pluck('id'));
            }
        }
    }
}
