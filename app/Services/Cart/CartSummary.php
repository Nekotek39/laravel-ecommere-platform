<?php

namespace App\Services\Cart;

use App\Enums\ShippingMethod;
use App\Models\CartItem;
use App\Models\Coupon;
use Illuminate\Support\Collection;

/**
 * Immutable cart summary. All amounts in cents.
 */
final readonly class CartSummary
{
    /**
     * @param  Collection<int, CartItem>  $items
     */
    public function __construct(
        public Collection $items,
        public int $subtotal,
        public int $discount,
        public ?int $shippingCost,
        public int $total,
        public ?Coupon $coupon = null,
        public ?string $couponError = null,
        public ?ShippingMethod $shippingMethod = null,
    ) {}

    public static function empty(): self
    {
        return new self(collect(), 0, 0, null, 0);
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function itemsCount(): int
    {
        return (int) $this->items->sum('quantity');
    }

    /**
     * Amount missing for free shipping, or 0 when the threshold has been reached.
     */
    public function missingForFreeShipping(): int
    {
        $threshold = (int) config('shop.shipping.free_shipping_threshold', 0);

        return max(0, $threshold - ($this->subtotal - $this->discount));
    }
}
