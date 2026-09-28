<?php

namespace App\Enums;

enum ShippingMethod: string
{
    case Courier = 'courier';
    case ParcelLocker = 'parcel_locker';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Courier => 'Courier',
            self::ParcelLocker => 'Parcel locker',
            self::Pickup => 'In-store pickup',
        };
    }

    /**
     * Base shipping cost in cents.
     */
    public function cost(): int
    {
        return (int) config("shop.shipping.methods.{$this->value}", 0);
    }

    /**
     * Shipping cost for a cart of the given value (after discount).
     */
    public function costFor(int $amount): int
    {
        $threshold = (int) config('shop.shipping.free_shipping_threshold', 0);

        if ($threshold > 0 && $amount >= $threshold) {
            return 0;
        }

        return $this->cost();
    }
}
