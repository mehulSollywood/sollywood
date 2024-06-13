<?php

namespace App\Http\Requests\Admin\PaymentPayload;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'payment_id' => [
                'required',
                'integer',
                Rule::exists('payments', 'id')
                    ->whereNotIn('tag',['wallet', 'cash']),
                Rule::unique('payment_payloads', 'payment_id')
            ],
            'payload' => 'required|array',
            'payload.*' => ['required']
        ];
    }
}
