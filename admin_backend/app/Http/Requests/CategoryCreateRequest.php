<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryCreateRequest extends FormRequest
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
            'keywords' => ['string'],
            'referralPercentage' => ['numeric'],
            'gstPercentage' => ['numeric'],
            'parent_id' => ['numeric'],
            'type' => ['required', Rule::in(Category::TYPES)],
            'active' => ['numeric', Rule::in(1,0)],
            "title"    => ['required', 'array'],
            "title.*"  => ['required', 'string', 'min:2', 'max:255'],
            "description"  => ['array'],
            "description.*"  => ['string', 'min:2'],
        ];
    }

}
