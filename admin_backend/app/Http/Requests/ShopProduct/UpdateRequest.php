<?php

namespace App\Http\Requests\ShopProduct;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'product_id' => 'required|integer|exists:products,id',
            'min_qty' => 'required|numeric|gte:0',
            'max_qty' => 'required|numeric|gte:min_qty',
            'active' => 'required|boolean',
            'quantity' => 'required|numeric|gte:0',
            'price' => 'required|numeric',
            'tax' => 'nullable|numeric'
        ];
    }
}
