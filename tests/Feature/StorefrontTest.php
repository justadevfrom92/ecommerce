<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_sliders_logo_cart_and_footer(): void
    {
        $category = Category::factory()->create();
        Product::factory()->featured()->create(['category_id' => $category->id, 'name' => 'Slider Star']);
        $this->seed(SettingsSeeder::class);

        $this->get('/')->assertOk()
            ->assertSee('data-slider', false)
            ->assertSee('Slider Star')
            ->assertSee('images/logo.svg', false)
            ->assertSee('Your cart')
            ->assertSee('&copy; '.date('Y').' MyStore', false);
    }

    public function test_search_finds_products_and_paginates(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(30)->create(['category_id' => $category->id]);
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Unique Walrus Lamp']);

        $this->get('/search?q=walrus')->assertOk()->assertSee('Unique Walrus Lamp');
        $this->get('/search')->assertOk()->assertSeeInOrder(['Showing', '1', 'to', '24', 'of', '31', 'products'], false);
    }

    public function test_inactive_products_are_hidden(): void
    {
        $product = Product::factory()->create(['is_active' => false, 'name' => 'Secret Thing']);

        $this->get('/products/'.$product->slug)->assertNotFound();
        $this->get('/search?q=Secret')->assertDontSee('Secret Thing');
    }

    public function test_department_category_and_product_pages(): void
    {
        $product = Product::factory()->create();
        $category = $product->category;

        $this->get('/departments/'.$category->department->slug)->assertOk()->assertSee($category->name);
        $this->get('/categories/'.$category->slug)->assertOk()->assertSee($product->name);
        $this->get('/products/'.$product->slug)->assertOk()->assertSee($product->sku);
    }

    public function test_cart_add_update_remove(): void
    {
        $product = Product::factory()->create(['stock' => 3, 'price' => 10]);

        $this->post('/cart', ['product_id' => $product->id, 'quantity' => 2])->assertSessionHas('status');
        $this->get('/cart')->assertOk()->assertSee($product->name)->assertSee('$20.00');

        // Can't exceed stock.
        $this->patch('/cart/'.$product->slug, ['quantity' => 9]);
        $this->assertSame([$product->id => 3], session('cart'));

        $this->delete('/cart/'.$product->slug);
        $this->assertSame([], session('cart'));
    }

    public function test_out_of_stock_cannot_be_added(): void
    {
        $product = Product::factory()->outOfStock()->create();

        $this->post('/cart', ['product_id' => $product->id])->assertSessionHas('error');
        $this->assertEmpty(session('cart', []));
    }
}
