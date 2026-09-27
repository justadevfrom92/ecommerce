<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_customers_are_forbidden(): void
    {
        $this->seedPermissions();
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_super_admin_sees_everything(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin')->assertRedirect(route('admin.orders.index'));
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/products')->assertOk();
    }

    public function test_section_permissions_are_enforced(): void
    {
        $user = $this->userWithPermissions(['admin.access', 'products.view']);

        $this->actingAs($user)->get('/admin/products')->assertOk();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();

        // /admin opens the first section the role can use; the sidebar only lists what it can open.
        $this->actingAs($user)->get('/admin')->assertRedirect(route('admin.products.index'));
        $this->actingAs($user)->get('/admin/products')->assertOk()
            ->assertSee(route('admin.products.index'))
            ->assertDontSee(route('admin.users.index'));
    }

    public function test_users_table_paginates_searches_and_sorts(): void
    {
        $admin = $this->superAdmin();
        User::factory()->count(45)->create();
        User::factory()->create(['name' => 'Zelda Findme', 'email' => 'zelda@example.com']);

        $this->actingAs($admin)->get('/admin/users')
            ->assertOk()
            ->assertSeeInOrder(['Showing', '1', 'to', '20', 'of', '47', 'users'], false);

        $this->actingAs($admin)->get('/admin/users?page=3')
            ->assertSeeInOrder(['Showing', '41', 'to', '47', 'of', '47'], false);

        $this->actingAs($admin)->get('/admin/users?search=findme')
            ->assertSee('Zelda Findme')
            ->assertSeeInOrder(['Showing', '1', 'to', '1', 'of', '1'], false);

        $this->actingAs($admin)->get('/admin/users?sort=name&direction=desc&per_page=10')
            ->assertSeeInOrder(['Zelda Findme'])
            ->assertSee('10 / page');
    }

    public function test_table_ignores_unknown_sort_columns(): void
    {
        $admin = $this->superAdmin();
        Product::factory()->count(3)->create();

        $this->actingAs($admin)->get('/admin/products?sort=password&direction=asc;drop')->assertOk();
    }

    public function test_sales_report_page(): void
    {
        $admin = $this->superAdmin();
        Order::factory()->create(['status' => 'paid', 'total' => 120, 'created_at' => now()->subDay()]);

        $this->actingAs($admin)->get('/admin/sales')->assertOk()->assertSee('Revenue')->assertSee('$120.00');

        $noOrders = $this->userWithPermissions(['admin.access', 'products.view']);
        $this->actingAs($noOrders)->get('/admin/sales')->assertForbidden();
    }

    public function test_low_stock_table_lists_out_of_stock_first(): void
    {
        $admin = $this->superAdmin();
        Product::factory()->create(['name' => 'Few Left', 'stock' => 3]);
        Product::factory()->create(['name' => 'All Gone', 'stock' => 0]);
        Product::factory()->create(['name' => 'Plenty Here', 'stock' => 80]);

        $this->actingAs($admin)->get('/admin/stock')->assertOk()
            ->assertSeeInOrder(['All Gone', 'Out of stock', 'Few Left', '3 left'])
            ->assertDontSee('Plenty Here');
    }
}
