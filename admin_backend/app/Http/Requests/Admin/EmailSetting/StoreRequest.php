<?php

namespace App\Http\Requests\Admin\EmailSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'smtp_auth'     => 'boolean',
            'smtp_debug'    => 'boolean',
            'host'          => 'required|string',
            'port'          => 'required|integer',
            'username'      => 'required|string',
            'password'      => 'required|string',
            'from_to'       => 'required|string',
            'active'        => Rule::in(0,1),
            'from_site'     => 'string',
        ];
    }
}
