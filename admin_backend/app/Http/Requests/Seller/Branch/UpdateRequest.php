<?php

namespace App\Http\Requests\Seller\Branch;

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
            'title' => 'array',
            'address' => 'array',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
        ];
    }

}
