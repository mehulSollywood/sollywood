<?php

namespace App\Http\Requests\Admin\Product;

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
            'category_id' => 'required|integer|exists:categories,id',
            'brand_id' => 'required|integer|exists:brands,id',
            'unit_id' => 'required|integer|exists:units,id',
            'keywords' => 'nullable|string',
            'images' => 'required|array',
            'qr_code' => 'required|string|unique:products,qr_code,'.$this->product,
            'title' => 'required|array',
            'description' => 'required|array',
            'min_qty' => 'required|numeric',
            'max_qty' => 'required|numeric',
            'active' => 'required|boolean',
            'quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'tax' => 'required|numeric',
        ];
    }

}
