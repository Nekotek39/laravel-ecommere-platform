<?php

namespace Tests\Unit;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Support\Money;
use PHPUnit\Framework\TestCase;

class CouponTest extends TestCase
{
    private function coupon(array $attributes): Coupon
    {
        return (new Coupon)->forceFill([
            'code' => 'TEST',
            'is_active' => true,
            'used_count' => 0,
            ...$attributes,
        ]);
    }

    public function test_percent_discount(): void
    {
        $coupon = $this->coupon(['type' => CouponType::Percent, 'value' => 15]);

        $this->assertSame(1500, $coupon->discountFor(10000));
        $this->assertSame(149, $coupon->discountFor(999));
    }

    public function test_fixed_discount_never_exceeds_subtotal(): void
    {
        $coupon = $this->coupon(['type' => CouponType::Fixed, 'value' => 5000]);

        $this->assertSame(5000, $coupon->discountFor(10000));
        $this->assertSame(3000, $coupon->discountFor(3000));
    }

    public function test_usage_limit(): void
    {
        $coupon = $this->coupon(['type' => CouponType::Percent, 'value' => 10, 'max_uses' => 1, 'used_count' => 1]);

        $this->assertFalse($coupon->isValidFor(10000));
    }

    public function test_money_conversion(): void
    {
        $this->assertSame(19999, Money::toCents('199,99'));
        $this->assertSame(100000, Money::toCents('1 000'));
        $this->assertSame(1010, Money::toCents(10.1));
        $this->assertNull(Money::toCents(''));
        $this->assertSame('10.10', Money::toDecimal(1010));
    }
}
