<?php

namespace App\Http\Requests\Admin;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * For a percentage coupon `value` is a percentage (1-100), for a fixed one it is an amount in major currency units.
 */
class CouponRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => mb_strtoupper(trim((string) $this->input('code'))),
            'value' => is_string($this->input('value')) ? str_replace(',', '.', $this->input('value')) : $this->input('value'),
            'min_order_amount' => is_string($this->input('min_order_amount')) ? str_replace(',', '.', $this->input('min_order_amount')) : $this->input('min_order_amount'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:64', 'alpha_dash', Rule::unique(Coupon::class)->ignore($this->route('coupon'))],
            'type' => ['required', Rule::enum(CouponType::class)],
            'value' => [
                'required',
                'numeric',
                'gt:0',
                Rule::when($this->input('type') === CouponType::Percent->value, ['integer', 'max:100']),
            ],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function couponData(): array
    {
        $type = $this->enum('type', CouponType::class);

        return [
            ...$this->safe()->only(['code', 'type', 'max_uses', 'starts_at', 'expires_at', 'is_active']),
            'value' => $type === CouponType::Percent
                ? $this->integer('value')
                : Money::toCents($this->input('value')),
            'min_order_amount' => Money::toCents($this->input('min_order_amount')),
        ];
    }
}
