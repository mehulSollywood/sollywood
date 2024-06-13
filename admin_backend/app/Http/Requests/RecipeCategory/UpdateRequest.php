<?php

namespace App\Http\Requests\RecipeCategory;

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
            'status' => 'boolean',
            'parent_id' => 'integer|exists:recipe_categories,id',
            'title' => 'required|array',
            'title.*' => 'required|string',
            'image' => 'string',
            'description' => 'required|array',
            'description.*' => 'required|string'
        ];
    }

}
