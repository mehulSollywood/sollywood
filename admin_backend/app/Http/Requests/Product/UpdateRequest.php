<?php

namespace App\Http\Requests\Product;

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
            'category_id' => 'nullable|integer|exists:categories,id',
            'brand_id' => 'nullable|integer|exists:brands,id',
            'unit_id' => 'nullable|integer|exists:units,id',
            'keywords' => 'nullable|string',
            'images' => 'required|array',
            'qr_code' => 'required|string|unique:products,qr_code,'.$this->product,
            'title' => 'required|array',
            'description' => 'required|array',
        ];
    }

}
