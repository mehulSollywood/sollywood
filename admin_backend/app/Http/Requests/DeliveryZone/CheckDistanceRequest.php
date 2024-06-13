<?php

namespace App\Http\Requests\DeliveryZone;

use Illuminate\Foundation\Http\FormRequest;

class CheckDistanceRequest extends FormRequest
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
            'address'           => 'array|required',
            'address.latitude'  => 'numeric|required',
            'address.longitude' => 'numeric|required',
            'currency_id'       => 'integer|exists:currencies,id'
        ];
    }
}
