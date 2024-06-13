<?php

namespace App\Http\Requests\Seller\Warehouse;

use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'shop_product_id' => 'required|integer|exists:shop_products,id',
            'user_id'         => 'required|integer|exists:users,id',
            'note'            => 'nullable|string',
            'quantity'        => 'required|integer',
            'type'            => 'required|'.Rule::in(Warehouse::TYPES),
        ];
    }
}
