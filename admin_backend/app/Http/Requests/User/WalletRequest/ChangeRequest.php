<?php

namespace App\Http\Requests\User\WalletRequest;

use App\Models\WalletRequest;
use Illuminate\Foundation\Http\FormRequest;

class ChangeRequest extends FormRequest
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
            'status' => 'required|string|in:'.WalletRequest::PENDING.','.WalletRequest::APPROVED.','.WalletRequest::REJECTED
        ];
    }
}
