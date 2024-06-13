<?php

namespace App\Http\Requests\User\Deliveryman;

use App\Models\DeliveryManSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BecomeDeliverymanRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'shop_id'               => 'required|integer|exists:shops,id',
            'type_of_technique'     => 'string',
            'brand'                 => 'string',
            'model'                 => 'string',
            'number'                => 'string',
            'color'                 => 'string',
            'location'              => 'array',
            'location.latitude'     => is_array(request('location')) ? 'required|numeric' : 'numeric',
            'location.longitude'    => is_array(request('location')) ? 'required|numeric' : 'numeric',
            'images'                => 'array',
            'images.*'              => 'string',
        ];
    }
}
