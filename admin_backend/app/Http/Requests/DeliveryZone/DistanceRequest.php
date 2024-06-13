<?php

namespace App\Http\Requests\DeliveryZone;

use Illuminate\Foundation\Http\FormRequest;

class DistanceRequest extends FormRequest
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
            'origin'                => 'array|required',
            'origin.latitude'       => 'numeric|required',
            'origin.longitude'      => 'numeric|required',

            'destination'           => 'array|required',
            'destination.latitude'  => 'numeric|required',
            'destination.longitude' => 'numeric|required',
        ];
    }
}
