<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class KeyRequest extends FormRequest
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
                    'shop'          => ['required', 'not_in:0'],
                    'category'      => ['required', 'string', 'max:150'],
                    'key_type'      => ['required', 'not_in:0'],
                    'key'           => ['required', 'string', 'max:255'],
                    'language'      => ['required', 'not_in:0'],
                    'instruction'   => ['required', 'string'],
                ]; 
            }
            case 'PUT':
            {
                return [
                    'shop'          => ['required', 'not_in:0'],
                    'category'      => ['required', 'string', 'max:150'],
                    'key_type'      => ['required', 'not_in:0'],
                    'key'           => ['required', 'string', 'max:255'],
                    'language'      => ['required', 'not_in:0'],
                    'instruction'   => ['required', 'string'],
                ]; 
            }
            default:break;
        }
    }
}