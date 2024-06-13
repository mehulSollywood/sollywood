<?php

namespace App\Http\Requests\Seller\ShopClosedDate;

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
            'dates'     => 'array',
            'dates.*'   => 'date_format:Y-m-d',
        ];
    }
}
