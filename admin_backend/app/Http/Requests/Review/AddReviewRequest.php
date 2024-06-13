<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class AddReviewRequest extends FormRequest
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
    public function rules()
    {
        return [
            'rating'    => 'required|numeric',
            'comment'   => 'string',
            'images'    => 'array',
            'images.*'  => 'string',
        ];
    }
}
