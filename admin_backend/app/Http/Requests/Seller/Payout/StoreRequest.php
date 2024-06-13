<?php

namespace App\Http\Requests\Seller\Payout;

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
            'currency_id'   => 'required|exists:currencies,id',
            'payment_id'    => 'required|exists:payments,id',
            'cause'         => 'string',
            'price'         => 'required|numeric|min:0',
        ];
    }
}
