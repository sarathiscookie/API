<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ModuleSettingsRequest extends FormRequest
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
        switch($this->method()) {
            case 'POST':
            {
                return [
                    'supplier' => ['required', 'not_in:0'],
                    'bcc_name' => ['string', 'max:255'],
                    'bcc_email' => ['string', 'email', 'max:255'],
                    'email_subject' => ['required', 'string', 'max:255'],
                    'email_body' => ['required', 'string'],
                    'max_error' => ['number'],
                    'wait_mod_no' => ['number'],
                    'wait_mod_id' => ['number'],
                ];
            }
            case 'PUT':
            {
                return [
                    'max_error' => ['number'],
                    'wait_mod_no' => ['number'],
                    'wait_mod_id' => ['number'],
                ];
            }
            default:break;
        } 
    }
}
