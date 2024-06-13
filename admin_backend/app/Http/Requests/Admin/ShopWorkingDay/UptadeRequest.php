<?php

namespace App\Http\Requests\Admin\ShopWorkingDay;

use App\Models\ShopWorkingDay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UptadeRequest extends FormRequest
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
            'dates'             => 'required|array|max:7',
            'dates.*.from'      => 'required|string|min:1|max:5',
            'dates.*.to'        => 'required|string|min:1|max:5',
            'dates.*.disabled'  => 'boolean',
            'dates.*.day'       => ['required', Rule::in(ShopWorkingDay::DAYS)],
        ];
    }
}
