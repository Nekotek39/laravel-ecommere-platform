<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $address = [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'company' => null,
            'tax_id' => null,
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postal_code' => fake()->numerify('##-###'),
            'country' => 'PL',
            'phone' => fake()->phoneNumber(),
        ];

        return [
            'user_id' => User::factory(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'payment_method' => PaymentMethod::BankTransfer,
            'shipping_method' => ShippingMethod::Courier,
            'email' => fake()->safeEmail(),
            'phone' => $address['phone'],
            'shipping_address' => $address,
            'billing_address' => $address,
            'subtotal' => 0,
            'discount' => 0,
            'shipping_cost' => ShippingMethod::Courier->cost(),
            'total' => ShippingMethod::Courier->cost(),
        ];
    }

    /**
     * Adds order items and recalculates the totals.
     */
    public function withItems(int $count = 2): static
    {
        return $this->afterCreating(function (Order $order) use ($count) {
            $products = Product::query()->inRandomOrder()->limit($count)->get();

            if ($products->isEmpty()) {
                $products = Product::factory()->count($count)->create();
            }

            foreach ($products as $product) {
                $quantity = fake()->numberBetween(1, 3);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'total' => $product->price * $quantity,
                ]);
            }

            $subtotal = (int) $order->items()->sum('total');
            $shipping = $order->shipping_method->costFor($subtotal);

            $order->update([
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'total' => $subtotal + $shipping,
            ]);
        });
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
