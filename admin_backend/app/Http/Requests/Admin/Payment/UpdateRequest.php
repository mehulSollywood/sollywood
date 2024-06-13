<?php

namespace App\Http\Requests\Admin\Payment;

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
            'client_id' => 'nullable',
            'secret_id' => 'nullable',
            'sandbox' => 'nullable',
            'merchant_email' => 'nullable',
            'payment_key' => 'nullable',
            'title' => 'required|array',
        ];
    }
}
