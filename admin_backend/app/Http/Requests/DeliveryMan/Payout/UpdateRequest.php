<?php

namespace App\Http\Requests\DeliveryMan\Payout;

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
            'created_by'    => 'exists:users,id',
            'currency_id'   => 'exists:currencies,id',
            'payment_id'    => 'exists:payments,id',
            'cause'         => 'string',
            'answer'        => 'string',
            'price'         => 'numeric|min:0',
        ];
    }
}
