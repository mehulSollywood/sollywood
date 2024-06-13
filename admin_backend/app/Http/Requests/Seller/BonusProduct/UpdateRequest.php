<?php

namespace App\Http\Requests\Seller\BonusProduct;

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
            'shop_product_id' => 'required|integer|exists:shop_products,id',
            'bonus_product_id' => 'required|integer|exists:shop_products,id',
            'bonus_quantity' => 'required|numeric|min:1',
            'shop_product_quantity' => 'required|numeric|min:1',
            'expired_at' => 'required|date_format:Y-m-d',
            'status' => 'required|boolean'


        ];
    }

}
