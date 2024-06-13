<?php

namespace App\Http\Requests\Seller\Order;

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
            'currency_id' => 'required|integer|exists:currencies,id',
            'rate' => 'required|integer',
            'delivery_fee' => 'nullable|integer',
            'coupon' => 'nullable|string',
            'delivery_date' => 'required|date|date_format:Y-m-d',
            'delivery_time' => 'nullable|string',
            'delivery_address_id' => 'nullable|integer|exists:user_addresses,id',
            'deliveryman' => 'nullable|integer|exists:users,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'delivery_type_id' => 'required|integer|exists:deliveries,id',
            'note' => 'nullable|string|max:191',
            'products' => 'nullable|array',
            'products.*.shop_product_id' => 'required|numeric',
            'products.*.qty' => 'required|numeric',
        ];
    }
}
