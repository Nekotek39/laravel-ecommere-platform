<?php

namespace App\Models;

use App\Enums\CouponType;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'integer',
            'min_order_amount' => 'integer',
            'max_uses' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected function code(): Attribute
    {
        return Attribute::set(fn (string $value) => mb_strtoupper(trim($value)));
    }

    public function scopeCode(Builder $query, string $code): void
    {
        $query->where('code', mb_strtoupper(trim($code)));
    }

    /**
     * Returns the reason the coupon cannot be used, or null when it is valid.
     */
    public function invalidReason(int $subtotal): ?string
    {
        return match (true) {
            ! $this->is_active => 'This discount code is inactive.',
            $this->starts_at !== null && $this->starts_at->isFuture() => 'This discount code is not active yet.',
            $this->expires_at !== null && $this->expires_at->isPast() => 'This discount code has expired.',
            $this->max_uses !== null && $this->used_count >= $this->max_uses => 'This discount code has reached its usage limit.',
            $this->min_order_amount !== null && $subtotal < $this->min_order_amount => 'The minimum order value for this code is '.Money::format($this->min_order_amount).'.',
            default => null,
        };
    }

    public function isValidFor(int $subtotal): bool
    {
        return $this->invalidReason($subtotal) === null;
    }

    /**
     * Discount amount in cents for the given cart value.
     */
    public function discountFor(int $subtotal): int
    {
        $discount = match ($this->type) {
            CouponType::Percent => intdiv($subtotal * min($this->value, 100), 100),
            CouponType::Fixed => $this->value,
        };

        return max(0, min($discount, $subtotal));
    }

    protected function formattedValue(): Attribute
    {
        return Attribute::get(fn () => $this->type === CouponType::Percent
            ? "{$this->value}%"
            : Money::format($this->value));
    }
}
