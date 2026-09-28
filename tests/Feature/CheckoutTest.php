<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderForAdmin;
use App\Notifications\OrderPlacedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    private function checkoutPayload(array $overrides = []): array
    {
        return array_replace_recursive([
            'email' => 'customer@example.com',
            'phone' => '500600700',
            'shipping_address' => [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'street' => 'Main Street 1',
                'city' => 'Warsaw',
                'postal_code' => '00-001',
                'country' => 'PL',
            ],
            'billing_same_as_shipping' => '1',
            'shipping_method' => 'courier',
            'payment_method' => 'bank_transfer',
            'terms' => '1',
        ], $overrides);
    }

    public function test_guest_can_place_order(): void
    {
        config(['shop.shipping.methods.courier' => 1500, 'shop.shipping.free_shipping_threshold' => 100000]);

        $product = Product::factory()->create(['price' => 4000, 'stock' => 5]);
        User::factory()->admin()->create();

        $this->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2]);

        $response = $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::query()->firstOrFail();

        $response->assertRedirect(route('checkout.success', $order));

        $this->assertSame(8000, $order->subtotal);
        $this->assertSame(1500, $order->shipping_cost);
        $this->assertSame(9500, $order->total);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('Warsaw', $order->shipping_address['city']);
        $this->assertSame($order->shipping_address, $order->billing_address);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);

        Notification::assertSentOnDemand(OrderPlacedNotification::class);
        Notification::assertSentTimes(NewOrderForAdmin::class, 1);
    }

    public function test_free_shipping_above_threshold(): void
    {
        config(['shop.shipping.free_shipping_threshold' => 10000]);

        $product = Product::factory()->create(['price' => 12000, 'stock' => 5]);

        $this->post(route('cart.items.store'), ['product_id' => $product->id]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $this->assertSame(0, Order::query()->firstOrFail()->shipping_cost);
    }

    public function test_logged_in_user_can_checkout_with_saved_address_and_coupon(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create(['city' => 'Krakow']);
        $product = Product::factory()->create(['price' => 10000, 'stock' => 5]);
        $coupon = Coupon::factory()->fixed(2000)->create(['code' => 'MINUS20']);

        $this->actingAs($user);
        $this->post(route('cart.items.store'), ['product_id' => $product->id]);
        $this->post(route('cart.coupon.apply'), ['code' => 'MINUS20']);

        $payload = $this->checkoutPayload(['address_id' => $address->id, 'shipping_method' => 'pickup']);
        unset($payload['shipping_address']);

        $this->post(route('checkout.store'), $payload)->assertRedirect();

        $order = $user->orders()->firstOrFail();

        $this->assertSame('Krakow', $order->shipping_address['city']);
        $this->assertSame(2000, $order->discount);
        $this->assertSame(8000, $order->total);
        $this->assertSame('MINUS20', $order->coupon_code);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_cannot_use_address_of_another_user(): void
    {
        $user = User::factory()->create();
        $foreignAddress = Address::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user);
        $this->post(route('cart.items.store'), ['product_id' => $product->id]);

        $payload = $this->checkoutPayload(['address_id' => $foreignAddress->id]);
        unset($payload['shipping_address']);

        $this->post(route('checkout.store'), $payload)->assertSessionHasErrors('address_id');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_fails_when_stock_changed(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 3]);

        $product->update(['stock' => 1]);

        $this->post(route('checkout.store'), $this->checkoutPayload())->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
    }

    public function test_separate_billing_address_is_required_when_requested(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.items.store'), ['product_id' => $product->id]);

        $this->post(route('checkout.store'), $this->checkoutPayload(['billing_same_as_shipping' => '0']))
            ->assertSessionHasErrors('billing_address.street');
    }

    public function test_customer_can_cancel_pending_order_and_stock_is_restored(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user);
        $this->post(route('cart.items.store'), ['product_id' => $product->id, 'quantity' => 2]);
        $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = $user->orders()->firstOrFail();
        $this->assertSame(3, $product->fresh()->stock);

        $this->post(route('account.orders.cancel', $order))->assertRedirect();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_customer_cannot_cancel_foreign_order(): void
    {
        $order = Order::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('account.orders.cancel', $order))
            ->assertForbidden();
    }
}
