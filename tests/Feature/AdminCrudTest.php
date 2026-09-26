<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\HomeSlider;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_page_renders(): void
    {
        $admin = $this->superAdmin();
        $product = Product::factory()->create();
        $order = Order::factory()->withItems()->create();
        $slider = HomeSlider::create(['title' => 'X', 'source' => 'featured']);

        $pages = [
            '/admin', '/admin/products', '/admin/products/create', "/admin/products/{$product->slug}/edit",
            '/admin/departments', '/admin/departments/create', "/admin/departments/{$product->category->department->slug}/edit",
            '/admin/categories', '/admin/categories/create', "/admin/categories/{$product->category->slug}/edit",
            '/admin/orders', "/admin/orders/{$order->number}",
            '/admin/users', '/admin/users/create', "/admin/users/{$order->user_id}/edit",
            '/admin/roles', '/admin/roles/create', '/admin/roles/1/edit',
            '/admin/settings', '/admin/sliders', '/admin/sliders/create', "/admin/sliders/{$slider->id}/edit",
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertOk();
        }
    }

    public function test_product_create_update_with_image_and_delete(): void
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Trail Runner 2', 'sku' => 'TR-2', 'category_id' => $category->id,
            'price' => '89.99', 'compare_at_price' => '120', 'stock' => 12,
            'is_active' => '1', 'is_featured' => '1',
            'image' => UploadedFile::fake()->image('shoe.jpg'),
        ])->assertSessionHasNoErrors();

        $product = Product::where('sku', 'TR-2')->firstOrFail();
        $this->assertSame('trail-runner-2', $product->slug);
        $this->assertTrue($product->is_featured);
        Storage::disk('public')->assertExists($product->image_path);

        $this->actingAs($admin)->put("/admin/products/{$product->slug}", [
            'name' => 'Trail Runner 2', 'sku' => 'TR-2', 'category_id' => $category->id,
            'price' => '79.99', 'stock' => 3, 'is_active' => '0', 'remove_image' => '1',
        ])->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertEquals(79.99, $product->price);
        $this->assertFalse($product->is_active);
        $this->assertNull($product->image_path);

        $this->actingAs($admin)->delete("/admin/products/{$product->slug}");
        $this->assertModelMissing($product);
    }

    public function test_compare_price_must_exceed_price(): void
    {
        $admin = $this->superAdmin();
        $category = Category::factory()->create();

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'X', 'sku' => 'X-1', 'category_id' => $category->id, 'price' => 50, 'compare_at_price' => 40, 'stock' => 1,
        ])->assertSessionHasErrors('compare_at_price');
    }

    public function test_department_with_products_cannot_be_deleted(): void
    {
        $admin = $this->superAdmin();
        $product = Product::factory()->create();
        $department = $product->category->department;

        $this->actingAs($admin)->delete("/admin/departments/{$department->slug}")->assertSessionHas('error');
        $this->assertModelExists($department);

        $empty = Department::factory()->create();
        $this->actingAs($admin)->delete("/admin/departments/{$empty->slug}")->assertSessionHas('status');
        $this->assertModelMissing($empty);
    }

    public function test_category_crud(): void
    {
        $admin = $this->superAdmin();
        $department = Department::factory()->create();

        $this->actingAs($admin)->post('/admin/categories', [
            'department_id' => $department->id, 'name' => 'Rain Jackets', 'sort_order' => 1, 'is_active' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => 'rain-jackets', 'department_id' => $department->id]);
    }

    public function test_order_status_update_restocks_on_cancel(): void
    {
        $admin = $this->superAdmin();
        $product = Product::factory()->create(['stock' => 1]);
        $order = Order::factory()->create(['status' => 'paid']);
        $order->items()->create(['product_id' => $product->id, 'name' => 'x', 'sku' => 'x', 'price' => 1, 'quantity' => 2, 'line_total' => 2]);

        $this->actingAs($admin)->patch("/admin/orders/{$order->number}", ['status' => 'cancelled', 'admin_note' => 'Customer asked'])
            ->assertSessionHas('status');

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('Customer asked', $order->fresh()->admin_note);
        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_view_only_staff_cannot_change_orders(): void
    {
        $user = $this->userWithPermissions(['admin.access', 'orders.view']);
        $order = Order::factory()->create(['status' => 'paid']);

        $this->actingAs($user)->get("/admin/orders/{$order->number}")->assertOk()->assertDontSee('Update order');
        $this->actingAs($user)->patch("/admin/orders/{$order->number}", ['status' => 'shipped'])->assertForbidden();
    }

    public function test_user_create_with_roles(): void
    {
        $admin = $this->superAdmin();
        $manager = Role::where('slug', 'store-manager')->first();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Sam Staff', 'email' => 'sam@example.com', 'password' => 'long-password-1',
            'password_confirmation' => 'long-password-1', 'roles' => [$manager->id], 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $sam = User::where('email', 'sam@example.com')->first();
        $this->assertTrue($sam->can('products.manage'));
        $this->assertFalse($sam->can('roles.manage'));
    }

    public function test_non_super_cannot_grant_super_or_edit_super_admins(): void
    {
        $manager = $this->userWithPermissions(['admin.access', 'users.view', 'users.manage']);
        $super = Role::where('slug', 'super-admin')->first();
        $target = User::factory()->create();

        $this->actingAs($manager)->put("/admin/users/{$target->id}", [
            'name' => $target->name, 'email' => $target->email, 'roles' => [$super->id],
        ])->assertSessionHasErrors('roles.0');
        $this->assertFalse($target->fresh()->isSuperAdmin());

        $boss = User::factory()->create();
        $boss->roles()->attach($super);
        $this->actingAs($manager)->get("/admin/users/{$boss->id}/edit")->assertForbidden();
    }

    public function test_cannot_remove_own_super_role_or_deactivate_self(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'roles' => [], 'is_active' => '0',
        ]);

        $admin->refresh();
        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->is_active);

        $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertSessionHas('error');
        $this->assertModelExists($admin);
    }

    public function test_role_permissions_and_protected_roles(): void
    {
        $admin = $this->superAdmin();
        $ids = Permission::whereIn('slug', ['admin.access', 'emails.inbox'])->pluck('id')->all();

        $this->actingAs($admin)->post('/admin/roles', ['name' => 'Inbox Only', 'permissions' => $ids])->assertSessionHasNoErrors();

        $role = Role::where('slug', 'inbox-only')->firstOrFail();
        $this->assertEqualsCanonicalizing(['admin.access', 'emails.inbox'], $role->permissions->pluck('slug')->all());

        $customer = Role::where('slug', 'customer')->first();
        $this->actingAs($admin)->delete("/admin/roles/{$customer->id}")->assertSessionHas('error');
        $this->assertModelExists($customer);

        $this->actingAs($admin)->delete("/admin/roles/{$role->id}")->assertSessionHas('status');
        $this->assertModelMissing($role);
    }

    public function test_settings_update_changes_store_name(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put('/admin/settings', [
            'store_name' => 'Acme Goods', 'store_email' => 'hi@acme.test', 'currency' => 'CAD', 'currency_symbol' => 'C$',
            'shipping_flat_rate' => 7, 'free_shipping_over' => 100, 'tax_rate' => 13,
        ])->assertSessionHasNoErrors();

        $this->assertSame('cad', Setting::get('currency'));
        $this->get('/')->assertSee('&copy; '.date('Y').' Acme Goods', false);
    }

    public function test_home_slider_for_a_category(): void
    {
        $admin = $this->superAdmin();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Category Pick']);

        $this->actingAs($admin)->post('/admin/sliders', [
            'title' => 'Hand picked', 'source' => 'category', 'category_id' => $category->id,
            'max_items' => 8, 'sort_order' => 0, 'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->get('/')->assertSee('Hand picked')->assertSee('Category Pick');

        $this->actingAs($admin)->post('/admin/sliders', [
            'title' => 'Broken', 'source' => 'category', 'max_items' => 8, 'sort_order' => 0,
        ])->assertSessionHasErrors('category_id');
    }
}
