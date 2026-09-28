<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Prices are entered in the form in major currency units (e.g. "199.99")
 * and stored in cents.
 */
class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'price' => $this->normalizeDecimal($this->input('price')),
            'compare_at_price' => $this->normalizeDecimal($this->input('compare_at_price')),
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');

        return [
            'category_id' => ['nullable', 'integer', Rule::exists(Category::class, 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(Product::class)->ignore($product)],
            'sku' => ['required', 'string', 'max:64', Rule::unique(Product::class)->ignore($product)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:65000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'compare_at_price' => ['nullable', 'numeric', 'gt:price', 'max:9999999'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productData(): array
    {
        return [
            ...$this->safe()->except(['images', 'price', 'compare_at_price']),
            'price' => Money::toCents($this->input('price')),
            'compare_at_price' => Money::toCents($this->input('compare_at_price')),
        ];
    }

    private function normalizeDecimal(mixed $value): mixed
    {
        return is_string($value) ? str_replace([' ', ','], ['', '.'], $value) : $value;
    }
}
