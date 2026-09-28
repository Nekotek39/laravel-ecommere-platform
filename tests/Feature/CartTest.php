<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_product_to_cart(): void
    {
        $product = Product::factory()->create(['price' => 5000, 'stock' => 10]);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk()
            ->assertJsonPath('cart.count', 2)
            ->assertJsonPath('cart.subtotal', 10000);

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_adding_same_product_increases_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 1]);
        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 3])
            ->assertJsonPath('cart.count', 4);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_cannot_add_more_than_stock(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 3])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');
    }

    public function test_cannot_add_inactive_product(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id])
            ->assertUnprocessable();
    }

    public function test_quantity_can_be_updated_and_item_removed(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id]);

        $this->patchJson(route('cart.items.update', $product), ['quantity' => 5])
            ->assertJsonPath('cart.count', 5);

        $this->deleteJson(route('cart.items.destroy', $product))
            ->assertJsonPath('cart.count', 0);
    }

    public function test_coupon_is_applied_to_cart(): void
    {
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);
        Coupon::factory()->create(['code' => 'SAVE10', 'value' => 10]);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id]);

        $this->postJson(route('cart.coupon.apply'), ['code' => 'save10'])
            ->assertOk()
            ->assertJsonPath('cart.discount', 1000)
            ->assertJsonPath('cart.total', 9000);
    }

    public function test_expired_coupon_is_rejected(): void
    {
        $product = Product::factory()->create(['stock' => 10]);
        Coupon::factory()->expired()->create(['code' => 'OLDCODE']);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id]);

        $this->postJson(route('cart.coupon.apply'), ['code' => 'OLDCODE'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('code');
    }

    public function test_guest_cart_is_merged_after_login(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $other = Product::factory()->create(['stock' => 10]);

        $userCart = Cart::query()->create(['user_id' => $user->id]);
        $userCart->items()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->postJson(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2]);
        $this->postJson(route('cart.items.store'), ['product_id' => $other->id, 'quantity' => 1]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect();

        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $userCart->id, 'product_id' => $product->id, 'quantity' => 3]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $userCart->id, 'product_id' => $other->id, 'quantity' => 1]);
    }
}
