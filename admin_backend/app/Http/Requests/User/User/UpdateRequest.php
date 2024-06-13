<?php

namespace App\Http\Requests\User\User;

use App\Models\User;
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
        $user = User::firstWhere('uuid',$this->uuid);

        return [
            'email' => 'nullable|email|unique:users,email,'.$user?->id,
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'phone' => 'nullable|numeric|unique:users,phone,'.$user?->id,
        ];
    }
}
