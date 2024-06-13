<?php

namespace App\Http\Requests\Refund;

use App\Models\Refund;
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
            'order_id' => 'required|integer|exists:orders,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'status' => ['nullable','string',Rule::in(Refund::STATUS)],
            'message_seller' => 'nullable|string',
            'message_user' => 'required|string',
            'images' => 'nullable|array',
        ];
    }

}
