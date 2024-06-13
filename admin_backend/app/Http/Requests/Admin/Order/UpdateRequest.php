<?php

namespace App\Http\Requests\Admin\Order;

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
            'total' => 'required|numeric',
            'currency_id' => 'required|integer|exists:currencies,id',
            'rate' => 'required|integer',
            'shop_id' => 'required|integer|exists:shops,id',
            'delivery_fee' => 'nullable|numeric',
            'coupon' => 'nullable|string',
            'tax' => 'required|numeric',
            'delivery_date' => 'required|date|date_format:Y-m-d',
            'delivery_time' => 'nullable|string',
            'delivery_address_id' => 'nullable|integer|exists:user_addresses,id',
            'deliveryman' => 'nullable|integer|exists:users,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'delivery_type_id' => 'required|integer|exists:deliveries,id',
            'total_discount' => 'nullable|numeric',
            'note' => 'nullable|string|max:191',
            'products' => 'nullable|array',
            'products.*.shop_product_id' => 'required|integer|exists:shop_products,id',
            'products.*.price' => 'required|numeric',
            'products.*.qty' => 'required|numeric',
            'products.*.tax' => 'nullable|numeric',
            'products.*.discount' => 'nullable|numeric',
            'products.*.total_price' => 'required|numeric',
            'products.*.extras' => 'nullable|array',

        ];
    }
}
