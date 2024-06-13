<?php

namespace App\Http\Requests;

use App\Models\Shop;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopCreateRequest extends FormRequest
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
            'status' => ['string', Rule::in(Shop::STATUS)],
            'active' => ['numeric', Rule::in(1,0)],

            "title"    => ['required', 'array'],
            "title.*"  => ['required', 'string', 'max:255'], // distinct 'min:2',
            "description"  => ['array'],
            "description.*"  => ['string'], // 'distinct'
            "address"    => ['required', 'array'],
            "address.*"  => ['string'], // distinct 'min:2'
        ];
    }
}
