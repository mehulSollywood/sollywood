<?php

namespace App\Http\Requests\DeliveryMan\Order;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;

class DebitOrderTransactionStatusChange extends FormRequest
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
            'status' => 'required|in:'.Transaction::REQUEST_PENDING
        ];
    }
}
