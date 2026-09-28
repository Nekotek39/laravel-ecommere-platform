<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // 0 removes the item from the cart
            'quantity' => ['required', 'integer', 'min:0', 'max:'.config('shop.max_cart_item_quantity')],
        ];
    }
}
