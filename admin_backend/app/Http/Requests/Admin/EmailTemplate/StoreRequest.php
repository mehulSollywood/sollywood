<?php

namespace App\Http\Requests\Admin\EmailTemplate;

use App\Models\EmailTemplate;
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
            'email_setting_id'  => 'required|exists:email_settings,id',
            'subject'           => 'required|string',
            'body'              => 'required',
            'alt_body'          => 'required|string',
            'send_to'           => 'required|date',
            'type'              => ['required', Rule::in(EmailTemplate::TYPES)],
        ];
    }
}
