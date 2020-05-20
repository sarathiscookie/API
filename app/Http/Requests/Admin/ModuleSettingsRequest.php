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
                    'wait_mod_id' => 'numeric',
                    'wait_mod_no' => 'numeric',
                    'max_error' => 'numeric',
                    'email_body' => 'required|string',
                    'email_subject' => 'required|string|max:255',
                    'bcc_email' => 'required|string|email|max:255',
                    'bcc_name' => 'required|string|max:255',
                    'supplier' => 'required|not_in:0',
                ];
            }
            case 'PUT':
            {
                return [
                    'wait_mod_id' => 'numeric',
                    'wait_mod_no' => 'numeric',
                    'max_error' => 'numeric',
                ];
            }
            default:break;
        } 
    }
}
