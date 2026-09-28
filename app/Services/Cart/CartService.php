<?php

namespace App\Services\Cart;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Shopping cart stored in the session.
 *
 * The session holds an array [product_id => quantity].
 */
class CartService
{
    public const SESSION_KEY = 'cart';

    /**
     * @return array<int, int>
     */
    public function raw(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = $this->raw();
        $newQuantity = ($cart[$product->id] ?? 0) + $quantity;

        $this->ensureInStock($product, $newQuantity);

        $cart[$product->id] = $newQuantity;
        session([self::SESSION_KEY => $cart]);
    }

    public function update(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->remove($product);

            return;
        }

        $this->ensureInStock($product, $quantity);

        $cart = $this->raw();
        $cart[$product->id] = $quantity;
        session([self::SESSION_KEY => $cart]);
    }

    public function remove(Product $product): void
    {
        $cart = $this->raw();
        unset($cart[$product->id]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Total number of pieces in the cart.
     */
    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function isEmpty(): bool
    {
        return $this->raw() === [];
    }

    /**
     * Cart items with their products loaded from the database.
     *
     * @return Collection<int, array{product: Product, quantity: int, total: float}>
     */
    public function items(): Collection
    {
        $cart = $this->raw();

        if ($cart === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->map(fn (Product $product) => [
                'product' => $product,
                'quantity' => $cart[$product->id],
                'total' => round($product->price * $cart[$product->id], 2),
            ])
            ->values();
    }

    public function total(): float
    {
        return round($this->items()->sum('total'), 2);
    }

    private function ensureInStock(Product $product, int $quantity): void
    {
        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$product->stock} of {$product->name} available.",
            ]);
        }
    }
}
