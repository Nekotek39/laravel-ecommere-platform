<?php

namespace App\Services\Cart;

use App\Enums\ShippingMethod;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The current customer's cart. A logged-in user has a cart attached to their account,
 * a guest has a cart whose ID is stored in the session.
 */
class CartService
{
    public const SESSION_KEY = 'cart_id';

    private ?Cart $cart = null;

    /**
     * Returns the existing cart without creating a new one.
     */
    public function find(): ?Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        if ($user = Auth::user()) {
            return $this->cart = Cart::query()->where('user_id', $user->id)->first();
        }

        $cartId = session(self::SESSION_KEY);

        return $this->cart = $cartId
            ? Cart::query()->whereNull('user_id')->find($cartId)
            : null;
    }

    /**
     * Returns the cart, creating it when needed.
     */
    public function current(): Cart
    {
        if ($cart = $this->find()) {
            return $cart;
        }

        if ($user = Auth::user()) {
            return $this->cart = Cart::query()->firstOrCreate(['user_id' => $user->id]);
        }

        $this->cart = Cart::query()->create();
        session([self::SESSION_KEY => $this->cart->id]);

        return $this->cart;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function items(): Collection
    {
        $cart = $this->find();

        if (! $cart) {
            return collect();
        }

        return $cart->items()
            ->with(['product.mainImage', 'product.category'])
            ->oldest()
            ->get();
    }

    public function count(): int
    {
        $cart = $this->find();

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    public function add(Product $product, int $quantity = 1): CartItem
    {
        if (! $product->isPurchasable()) {
            throw ValidationException::withMessages([
                'quantity' => 'This product is currently unavailable.',
            ]);
        }

        $item = $this->current()->items()->firstOrNew(['product_id' => $product->id]);
        $newQuantity = ($item->quantity ?? 0) + $quantity;

        $this->ensureQuantityAvailable($product, $newQuantity);

        $item->quantity = $newQuantity;
        $item->save();

        return $item;
    }

    public function update(Product $product, int $quantity): ?CartItem
    {
        if ($quantity <= 0) {
            $this->remove($product);

            return null;
        }

        $item = $this->current()->items()->where('product_id', $product->id)->firstOrFail();

        $this->ensureQuantityAvailable($product, $quantity);

        $item->update(['quantity' => $quantity]);

        return $item;
    }

    public function remove(Product $product): void
    {
        $this->find()?->items()->where('product_id', $product->id)->delete();
    }

    public function clear(): void
    {
        $cart = $this->find();

        if (! $cart) {
            return;
        }

        $cart->items()->delete();
        $cart->coupon()->dissociate()->save();
    }

    public function applyCoupon(string $code): Coupon
    {
        $coupon = Coupon::query()->code($code)->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['code' => 'Invalid discount code.']);
        }

        $subtotal = $this->subtotal($this->items());

        if ($reason = $coupon->invalidReason($subtotal)) {
            throw ValidationException::withMessages(['code' => $reason]);
        }

        $this->current()->coupon()->associate($coupon)->save();

        return $coupon;
    }

    public function removeCoupon(): void
    {
        $cart = $this->find();

        $cart?->coupon()->dissociate()->save();
    }

    /**
     * Removes unavailable products from the cart and reduces quantities exceeding
     * the stock level. Returns messages for the customer.
     *
     * @return list<string>
     */
    public function sanitize(): array
    {
        $messages = [];

        foreach ($this->items() as $item) {
            $product = $item->product;

            if (! $product || ! $product->isPurchasable()) {
                $item->delete();
                $messages[] = $product
                    ? "{$product->name} is unavailable and has been removed from your cart."
                    : 'One of the products is unavailable and has been removed from your cart.';

                continue;
            }

            if ($item->quantity > $product->stock) {
                $item->update(['quantity' => $product->stock]);
                $messages[] = "The quantity of {$product->name} has been reduced to {$product->stock} (available stock).";
            }
        }

        return $messages;
    }

    public function summary(?ShippingMethod $shippingMethod = null): CartSummary
    {
        $cart = $this->find();

        if (! $cart) {
            return CartSummary::empty();
        }

        $items = $this->items()->filter(fn (CartItem $item) => $item->product !== null)->values();
        $subtotal = $this->subtotal($items);

        $coupon = $cart->coupon;
        $couponError = null;
        $discount = 0;

        if ($coupon) {
            $couponError = $coupon->invalidReason($subtotal);
            $discount = $couponError === null ? $coupon->discountFor($subtotal) : 0;
        }

        $shippingCost = $shippingMethod?->costFor($subtotal - $discount);

        return new CartSummary(
            items: $items,
            subtotal: $subtotal,
            discount: $discount,
            shippingCost: $shippingCost,
            total: $subtotal - $discount + ($shippingCost ?? 0),
            coupon: $coupon,
            couponError: $couponError,
            shippingMethod: $shippingMethod,
        );
    }

    /**
     * Moves the guest cart contents into the user's cart after login.
     */
    public function mergeGuestCartInto(User $user): void
    {
        $guestCartId = session()->pull(self::SESSION_KEY);
        $this->cart = null;

        if (! $guestCartId) {
            return;
        }

        $guestCart = Cart::query()->whereNull('user_id')->with('items.product')->find($guestCartId);

        if (! $guestCart) {
            return;
        }

        DB::transaction(function () use ($guestCart, $user) {
            $userCart = Cart::query()->where('user_id', $user->id)->first();

            if (! $userCart) {
                $guestCart->update(['user_id' => $user->id]);

                return;
            }

            foreach ($guestCart->items as $guestItem) {
                if (! $guestItem->product) {
                    continue;
                }

                $item = $userCart->items()->firstOrNew(['product_id' => $guestItem->product_id]);
                $item->quantity = min(
                    ($item->quantity ?? 0) + $guestItem->quantity,
                    $guestItem->product->stock,
                    $this->maxQuantity(),
                );

                if ($item->quantity > 0) {
                    $item->save();
                }
            }

            if (! $userCart->coupon_id && $guestCart->coupon_id) {
                $userCart->update(['coupon_id' => $guestCart->coupon_id]);
            }

            $guestCart->delete();
        });
    }

    /**
     * Forgets the in-memory cart (e.g. after logout).
     */
    public function forget(): void
    {
        $this->cart = null;
    }

    /**
     * @param  Collection<int, CartItem>  $items
     */
    private function subtotal(Collection $items): int
    {
        return (int) $items->sum(fn (CartItem $item) => $item->product ? $item->total() : 0);
    }

    private function ensureQuantityAvailable(Product $product, int $quantity): void
    {
        if ($quantity > $this->maxQuantity()) {
            throw ValidationException::withMessages([
                'quantity' => "The maximum quantity of a single product in the cart is {$this->maxQuantity()}.",
            ]);
        }

        if ($quantity > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$product->stock} of {$product->name} available.",
            ]);
        }
    }

    private function maxQuantity(): int
    {
        return (int) config('shop.max_cart_item_quantity', 99);
    }
}
