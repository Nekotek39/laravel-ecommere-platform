<?php

namespace App\Http\Requests\Shop;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Models\Address;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Checkout form.
 *
 * A logged-in customer may pass the `address_id` of a saved address instead of
 * the `shipping_address.*` fields. The billing address is required only when
 * `billing_same_as_shipping` is false.
 */
class CheckoutRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'billing_same_as_shipping' => $this->boolean('billing_same_as_shipping', true),
        ]);
    }

    public function rules(): array
    {
        $shippingRequired = $this->filled('address_id') ? 'nullable' : 'required';

        return [
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],

            'address_id' => [
                'nullable',
                'integer',
                Rule::exists(Address::class, 'id')->where('user_id', $this->user()?->id ?? 0),
            ],

            ...$this->addressRules('shipping_address', [$shippingRequired]),

            'billing_same_as_shipping' => ['boolean'],
            ...$this->addressRules('billing_address', ['exclude_if:billing_same_as_shipping,true', 'required']),

            'shipping_method' => ['required', Rule::enum(ShippingMethod::class)],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms' => ['accepted'],
        ];
    }

    public function attributes(): array
    {
        return [
            'shipping_address.first_name' => 'first name',
            'shipping_address.last_name' => 'last name',
            'shipping_address.street' => 'street',
            'shipping_address.city' => 'city',
            'shipping_address.postal_code' => 'postal code',
            'shipping_address.country' => 'country',
            'billing_address.first_name' => 'billing first name',
            'billing_address.last_name' => 'billing last name',
            'billing_address.street' => 'billing street',
            'billing_address.city' => 'billing city',
            'billing_address.postal_code' => 'billing postal code',
            'billing_address.country' => 'billing country',
            'terms' => 'terms and conditions',
        ];
    }

    /**
     * Data ready to be passed to OrderService::placeOrder().
     *
     * @return array<string, mixed>
     */
    public function checkoutData(): array
    {
        $shipping = $this->filled('address_id')
            ? Address::query()->findOrFail($this->integer('address_id'))->toSnapshot()
            : $this->addressSnapshot('shipping_address');

        $shipping['phone'] ??= $this->input('phone');

        $billing = $this->boolean('billing_same_as_shipping')
            ? $shipping
            : $this->addressSnapshot('billing_address');

        return [
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'shipping_address' => $shipping,
            'billing_address' => $billing,
            'shipping_method' => $this->enum('shipping_method', ShippingMethod::class),
            'payment_method' => $this->enum('payment_method', PaymentMethod::class),
            'notes' => $this->input('notes'),
        ];
    }

    /**
     * @param  list<string>  $presence
     * @return array<string, list<string>>
     */
    private function addressRules(string $prefix, array $presence): array
    {
        $optional = array_values(array_diff($presence, ['required']));
        $optional[] = 'nullable';

        return [
            "{$prefix}.first_name" => [...$presence, 'string', 'max:100'],
            "{$prefix}.last_name" => [...$presence, 'string', 'max:100'],
            "{$prefix}.company" => [...$optional, 'string', 'max:255'],
            "{$prefix}.tax_id" => [...$optional, 'string', 'max:32'],
            "{$prefix}.street" => [...$presence, 'string', 'max:255'],
            "{$prefix}.city" => [...$presence, 'string', 'max:100'],
            "{$prefix}.postal_code" => [...$presence, 'string', 'max:20'],
            "{$prefix}.country" => [...$presence, 'string', 'size:2'],
            "{$prefix}.phone" => [...$optional, 'string', 'max:32'],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function addressSnapshot(string $prefix): array
    {
        $address = [];

        foreach (Address::SNAPSHOT_FIELDS as $field) {
            $address[$field] = $this->input("{$prefix}.{$field}");
        }

        $address['country'] = strtoupper((string) ($address['country'] ?? 'PL'));

        return $address;
    }
}
