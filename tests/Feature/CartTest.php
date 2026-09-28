<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_can_be_added_to_session_cart(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->post(route('cart.store', $product), ['quantity' => 2])
            ->assertSessionHas('cart', [$product->id => 2]);

        // Adding the same product again increases the quantity
        $this->post(route('cart.store', $product), ['quantity' => 1])
            ->assertSessionHas('cart', [$product->id => 3]);
    }

    public function test_cannot_add_more_than_stock(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $this->post(route('cart.store', $product), ['quantity' => 3])
            ->assertSessionHasErrors('quantity');

        $this->assertSame([], session('cart', []));
    }

    public function test_quantity_can_be_updated_and_product_removed(): void
    {
        $product = Product::factory()->create(['stock' => 10]);

        $this->post(route('cart.store', $product));

        $this->patch(route('cart.update', $product), ['quantity' => 5])
            ->assertSessionHas('cart', [$product->id => 5]);

        $this->delete(route('cart.destroy', $product))
            ->assertSessionHas('cart', []);
    }
}
