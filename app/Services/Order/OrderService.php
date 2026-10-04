<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private CartService $cart) {}

    /**
     * Places an order from the cart contents.
     *
     * Runs in a database transaction: checks stock levels, saves the order
     * with its items, decreases stock and finally empties the cart.
     *
     * @param  array{full_name: string, phone: string, address: string, city: string, postal_code: string, notes?: string|null}  $data
     */
    public function placeOrder(User $user, array $data): Order
    {
        $cart = $this->cart->raw();

        if ($cart === []) {
            throw ValidationException::withMessages(['cart' => 'Koszyk jest pusty.']);
        }
        if (!preg_match('/^[0-9]{2}-[0-9]{3}$/', $data['postal_code'])) {
            throw ValidationException::withMessages(['postal_code' => 'Nieprawidłowy kod pocztowy.']);
        }
        $order = DB::transaction(function () use ($user, $data, $cart) {
            // Lock the products so two customers cannot buy the last item at the same time.
            $products = Product::query()
                ->whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;

            foreach ($cart as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product || $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => 'Niektóre produkty w Twoim koszyku nie są już dostępne w żądanej ilości.',
                    ]);
                }

                $total += $product->price * $quantity;
            }

            $order = $user->orders()->create([
                ...$data,
                'total' => round($total, 2),
            ]);

            foreach ($cart as $productId => $quantity) {
                $product = $products->get($productId);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                ]);

                $product->decrement('stock', $quantity);
            }

            return $order;
        });

        $this->cart->clear();

        return $order;
    }
}
