<?php

namespace Tests;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function seedPermissions(): void
    {
        $this->seed(PermissionSeeder::class);
    }

    protected function superAdmin(): User
    {
        $this->seedPermissions();
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('slug', 'super-admin')->first());

        return $user;
    }

    /** A user whose only role grants exactly these permission slugs. */
    protected function userWithPermissions(array $slugs): User
    {
        $this->seedPermissions();
        $role = Role::create(['name' => 'Test role', 'slug' => 'test-'.uniqid()]);
        $role->permissions()->sync(Permission::whereIn('slug', $slugs)->pluck('id'));
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }
}
