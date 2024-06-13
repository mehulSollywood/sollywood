<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cart_id' => 'nullable|integer|exists:carts,id',
            'shop_id' => 'required|integer|exists:shops,id',
            'shop_product_id' => 'required|integer|exists:shop_products,id',
            'quantity' => 'required|numeric',
            'user_cart_uuid' => 'nullable|string'
        ];
    }

}

