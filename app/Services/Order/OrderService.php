<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Places an order from the cart contents.
     *
     * The operation is atomic: it locks the products and the coupon, verifies stock
     * levels, decrements them and empties the cart.
     *
     * @param  array{
     *     email: string,
     *     phone: string,
     *     shipping_address: array<string, mixed>,
     *     billing_address: array<string, mixed>,
     *     shipping_method: ShippingMethod,
     *     payment_method: PaymentMethod,
     *     notes?: string|null,
     * }  $data
     */
    public function placeOrder(Cart $cart, array $data, ?User $user = null): Order
    {
        $order = DB::transaction(function () use ($cart, $data, $user) {
            $items = $cart->items()->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
            }

            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'One of the products in your cart is no longer available. Please review your cart.',
                    ]);
                }

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Only {$product->stock} of {$product->name} available.",
                    ]);
                }

                $subtotal += $product->price * $item->quantity;
            }

            [$discount, $coupon] = $this->resolveDiscount($cart, $subtotal);

            /** @var ShippingMethod $shippingMethod */
            $shippingMethod = $data['shipping_method'];
            $shippingCost = $shippingMethod->costFor($subtotal - $discount);

            $order = Order::query()->create([
                'user_id' => $user?->id,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'payment_method' => $data['payment_method'],
                'shipping_method' => $shippingMethod,
                'email' => $data['email'],
                'phone' => $data['phone'],
                'shipping_address' => $data['shipping_address'],
                'billing_address' => $data['billing_address'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal - $discount + $shippingCost,
                'coupon_code' => $coupon?->code,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $item->quantity,
                    'total' => $product->price * $item->quantity,
                ]);

                $product->decrement('stock', $item->quantity);
            }

            $coupon?->increment('used_count');

            $cart->items()->delete();
            $cart->update(['coupon_id' => null]);

            return $order;
        });

        OrderPlaced::dispatch($order);

        return $order;
    }

    /**
     * Cancels the order and returns the products to stock.
     */
    public function cancel(Order $order): Order
    {
        if ($order->isCancelled()) {
            return $order;
        }

        $previousStatus = $order->status;

        DB::transaction(function () use ($order) {
            $order->loadMissing('items');

            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::withTrashed()->whereKey($item->product_id)->increment('stock', $item->quantity);
                }
            }

            if ($order->coupon_code) {
                Coupon::query()->code($order->coupon_code)->where('used_count', '>', 0)->decrement('used_count');
            }

            $order->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
            ]);
        });

        OrderStatusChanged::dispatch($order, $previousStatus);

        return $order;
    }

    /**
     * Changes the order status, respecting the allowed transitions.
     */
    public function updateStatus(Order $order, OrderStatus $status, ?string $trackingNumber = null): Order
    {
        if ($order->status === $status) {
            if ($trackingNumber !== null) {
                $order->update(['tracking_number' => $trackingNumber]);
            }

            return $order;
        }

        if (! $order->status->canTransitionTo($status)) {
            throw ValidationException::withMessages([
                'status' => "Cannot change the status from \"{$order->status->label()}\" to \"{$status->label()}\".",
            ]);
        }

        if ($status === OrderStatus::Cancelled) {
            return $this->cancel($order);
        }

        $previousStatus = $order->status;

        $order->fill([
            'status' => $status,
            'tracking_number' => $trackingNumber ?? $order->tracking_number,
        ]);

        match ($status) {
            OrderStatus::Shipped => $order->shipped_at = now(),
            OrderStatus::Delivered => $order->delivered_at = now(),
            default => null,
        };

        $order->save();

        OrderStatusChanged::dispatch($order, $previousStatus);

        return $order;
    }

    public function updatePaymentStatus(Order $order, PaymentStatus $status): Order
    {
        $order->update([
            'payment_status' => $status,
            'paid_at' => $status === PaymentStatus::Paid ? ($order->paid_at ?? now()) : $order->paid_at,
        ]);

        return $order;
    }

    /**
     * @return array{0: int, 1: Coupon|null}
     */
    private function resolveDiscount(Cart $cart, int $subtotal): array
    {
        if (! $cart->coupon_id) {
            return [0, null];
        }

        $coupon = Coupon::query()->lockForUpdate()->find($cart->coupon_id);

        if (! $coupon) {
            return [0, null];
        }

        if ($reason = $coupon->invalidReason($subtotal)) {
            throw ValidationException::withMessages(['code' => $reason]);
        }

        return [$coupon->discountFor($subtotal), $coupon];
    }
}
