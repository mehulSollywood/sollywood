<?php

namespace App\Http\Requests\ShopPayment;

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
            'payment_id' => 'required|integer|exists:payments,id',
            'status' => 'required|boolean',
            'client_id' => 'nullable|string',
            'secret_id' => 'nullable|string',
            'merchant_email' => 'nullable|string',
            'payment_key' => 'nullable|string',
        ];
    }
}
