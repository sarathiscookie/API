<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
                    'zip'           => ['required', 'string', 'max:20'],
                    'street'        => ['required', 'string', 'max:255'],
                    'city'          => ['required', 'string', 'max:255'],
                    'country'       => ['required', 'not_in:0'],
                    'phone'         => ['required', 'string', 'max:20'],
                    'user_company'  => ['required', 'not_in:0'],
                    'password'      => ['required', 'string', 'min:8', 'confirmed'],
                    'username'      => ['required', 'alpha_dash', 'string', 'max:255', 'unique:users'],
                    'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'name'          => ['required', 'string', 'max:255']
                ]; 
            }
            case 'PUT':
            {
                return [
                    'zip'           => ['required', 'string', 'max:20'],
                    'country'       => ['required', 'not_in:0'],
                    'city'          => ['required', 'string', 'max:255'],
                    'street'        => ['required', 'string', 'max:255'],
                    'user_company'  => ['required', 'not_in:0'],
                    'phone'         => ['required', 'string', 'max:20'],
                    'name'          => ['required', 'string', 'max:255']
                ]; 
            }
            default:break;
        }
    }
}
