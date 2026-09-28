<?php

namespace App\Http\Requests\Shop;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists(Product::class, 'id')->whereNull('deleted_at')],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:'.config('shop.max_cart_item_quantity')],
        ];
    }

    public function product(): Product
    {
        return Product::query()->findOrFail($this->integer('product_id'));
    }

    public function quantity(): int
    {
        return $this->integer('quantity', 1);
    }
}
