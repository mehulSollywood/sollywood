<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'currency_id'         => 'required|integer|exists:currencies,id',
            'rate'                => 'required|numeric',
            'shop_id'             => 'required|integer|exists:shops,id',
            'delivery_fee'        => 'nullable|numeric',
            'money_back'          => 'nullable|numeric',
            'coupon'              => 'nullable|string',
            'delivery_date'       => 'nullable|date|date_format:Y-m-d',
            'delivery_time'       => 'nullable|string',
            'delivery_address_id' => 'nullable|integer|exists:user_addresses,id',
            'deliveryman'         => 'nullable|integer|exists:users,id',
            'delivery_type_id'    => 'nullable|integer|exists:deliveries,id',
            'note'                => 'nullable|string|max:191',
            'cart_id'             => 'required|integer|exists:carts,id',
            'branch_id'           => 'nullable|integer|exists:branches,id',
            'name'                => 'nullable|string',
            'phone'               => 'nullable|string',
            'payment_sys_id'      => 'required_if:auto_order,true|integer',
            'gift_user_id'        => 'nullable|exists:users,id',
            'gift_cart_id'        => 'nullable|integer|exists:shop_products,id',
            'auto_order'          => 'nullable|boolean',
            'type'                => 'required_if:auto_order,true|in:1,2,3,fix',
            'date'                => 'array',
            'date.*.'             => 'date_format:Y-m-d H:i:s',
//            'date.*.start_date' => 'required|date_format:Y-m-d H:i:s'
        ];
    }
}
