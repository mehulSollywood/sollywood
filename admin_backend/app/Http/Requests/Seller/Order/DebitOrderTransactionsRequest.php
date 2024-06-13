<?php

namespace App\Http\Requests\Seller\Order;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class DebitOrderTransactionsRequest extends FormRequest
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
            'perPage' => 'required|integer',
            'status' => 'nullable|in:'
                .Transaction::REQUEST_WAITING.','
                .Transaction::REQUEST_PENDING.','
                .Transaction::REQUEST_APPROVED.','
                .Transaction::REQUEST_REJECT,
        ];
    }
}
