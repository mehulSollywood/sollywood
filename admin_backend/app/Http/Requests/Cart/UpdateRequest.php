<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'shop_id' => 'required|integer|exists:shops,id',
            'user_id' => 'required|integer|exists:users,id',
            'total_price' => 'required|numeric',
            'total_discount' => 'required|numeric',
            'shop_product_id' => 'required|integer|exists:shop_products,id',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric',
            'discount' => 'nullable|numeric',
        ];
    }
}
