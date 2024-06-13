<?php

namespace App\Http\Requests\Seller\Report;

use Illuminate\Foundation\Http\FormRequest;

class HistoryRequest extends FormRequest
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
            'type'      => 'required|in:deliveryman,today,history',
            'column'    => 'string|in:id,total_price,created_at,note,delivery_fee,commission_fee,user_id',
            'sort'      => 'string|in:asc,desc',
        ];
    }
}
