<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'category_id'    => 'nullable|integer|exists:categories,id',
            'brand_id'       => 'nullable|integer|exists:brands,id',
            'unit_id'        => 'nullable|integer|exists:units,id',
            'keywords'       => 'nullable|string',
            'images'         => 'required|array',
            'qr_code'        => 'required|string|unique:products,qr_code',
            'title'          => 'required|array',
            'description'    => 'required|array',
            'min_qty'        => 'required|numeric',
            'max_qty'        => 'required|numeric',
            'active'         => 'required|boolean',
            'quantity'       => 'required|numeric',
            'price'          => 'nullable|numeric',
            'tax'            => 'required|numeric',
            'gift'           => 'nullable|boolean'
        ];
    }

}
