<?php

namespace App\Http\Requests\Seller\BonusShop;

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
            'bonus_product_id' => 'required|integer|exists:shop_products,id',
            'bonus_quantity' => 'required|integer|min:1',
            'order_amount' => 'required|numeric|min:1',
            'expired_at' => 'required|date_format:Y-m-d',
            'status' => 'required'
        ];
    }

}
