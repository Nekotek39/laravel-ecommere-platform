<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function deliveryData(): array
    {
        return [
            'full_name' => 'John Smith',
            'phone' => '500600700',
            'address' => 'Main Street 1',
            'city' => 'Warsaw',
            'postal_code' => '00-001',
        ];
    }

    public function test_guest_must_log_in_to_order(): void
    {
        $this->get(route('checkout.create'))->assertRedirect(route('login'));
        $this->post(route('checkout.store'), $this->deliveryData())->assertRedirect(route('login'));
    }

    public function test_customer_can_place_order(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 40.50, 'stock' => 5]);

        $this->actingAs($user)->post(route('cart.store', $product), ['quantity' => 2]);

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->deliveryData());

        $order = Order::query()->firstOrFail();

        $response->assertRedirect(route('account.orders.show', $order));
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('81.00', $order->total);
        $this->assertSame('Warsaw', $order->city);
        $this->assertCount(1, $order->items);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame([], session('cart', []));
    }

    public function test_checkout_form_is_validated(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->post(route('cart.store', $product));

        $this->actingAs($user)->post(route('checkout.store'), [])
            ->assertSessionHasErrors(['full_name', 'phone', 'address', 'city', 'postal_code']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cannot_order_with_empty_cart(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('checkout.store'), $this->deliveryData())
            ->assertSessionHasErrors('cart');
    }

    public function test_customer_cannot_see_someone_elses_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('account.orders.show', $order))
            ->assertForbidden();
    }
}
