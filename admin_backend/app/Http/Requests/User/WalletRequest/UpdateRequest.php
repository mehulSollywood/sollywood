<?php

namespace App\Http\Requests\User\WalletRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'price'      => 'required|numeric',
            'user_phone' => 'required|exists:users,phone',
            'message'    => 'required|string|max:255',
        ];
    }
}
