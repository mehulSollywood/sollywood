<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCreateRequest extends FormRequest
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
        $userId = auth('sanctum')->user()->id;
        return [
            'firstname' => 'required|string|min:2|max:100',
            'email' => 'email|unique:users',
            'phone' => 'numeric|unique:users,phone,'.$userId,
            'gender' => ['string', Rule::in('male', 'female')],
            'active' => ['numeric', Rule::in(1, 0)],
            'password' => 'min:6,confirmed'
        ];
    }
}
