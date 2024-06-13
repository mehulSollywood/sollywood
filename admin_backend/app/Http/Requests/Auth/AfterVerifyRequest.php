<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AfterVerifyRequest extends FormRequest
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
            'password'  => 'string',
            'email'     => [
                'email',
                Rule::unique('users', 'email')
            ],
            'firstname' => 'string|min:2|max:100',
            'lastname' => 'string|min:2|max:100',
            'referral'  => 'string|exists:users,my_referral|max:255',
        ];
    }
}
