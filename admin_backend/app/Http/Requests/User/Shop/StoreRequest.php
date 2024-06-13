<?php

namespace App\Http\Requests\User\Shop;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'title'    => 'required|array',
            'title.*'  => 'required|string|max:255',
            'description'  => 'array',
            'description.*'  => 'required|string',
            'address'    => 'required|array',
            'address.*'  => 'required|string',
            'images'    => 'required|array',
            'images.*'  => 'required|string',
            'phone' => 'required',
            'location' => 'required',
            'open' => 'nullable',
            'type_of_business' => 'nullable|string',
            'category' => 'nullable|string',
            'commission' => 'nullable|numeric',
            'adhar' => 'nullable|string',
            'pan' => 'nullable|string',
            'business_res_certi' => 'nullable|string',
            'gst' => 'nullable|string'
        ];
    }
}
