<?php

namespace App\Http\Requests\Recipe;

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
            'recipe_category_id' => 'required|integer|exists:recipe_categories,id',
            'title' => 'required|array',
            'active_time' => 'nullable|numeric',
            'total_time' => 'nullable|numeric',
            'calories' => 'nullable|numeric',
            'image' => 'nullable|string',
            'instruction' => 'nullable|array',
            'nutrition' => 'nullable|array',
            'products' => 'required|array',
        ];
    }

}
