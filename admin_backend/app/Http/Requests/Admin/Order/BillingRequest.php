<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class BillingRequest extends FormRequest
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
            'perPage'          => 'required|integer',
            'date_from'        => 'required|date_format:Y-m-d',
            'date_to'          => 'date_format:Y-m-d',
            'search'           => 'string',
            'shop_id'          => 'nullable|integer|exists:shops,id',
            'deliveryman_id'   => 'nullable|integer|exists:users,id',
        ];
    }
}
