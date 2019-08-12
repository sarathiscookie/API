<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
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
                    'supplier_zip'     => ['required', 'string', 'max:20'],
                    'supplier_street'  => ['required', 'string', 'max:255'],
                    'supplier_city'    => ['required', 'string', 'max:255'],
                    'supplier_country' => ['required', 'not_in:0'],
                    'supplier_phone'   => ['required', 'string', 'max:20'],
                    'supplier_company' => ['required', 'not_in:0'],
                    'email'            => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'supplier_name'    => ['required', 'string', 'max:255']
                ]; 
            }
            case 'PUT':
            {
                return [
                    'supplier_zip'     => ['required', 'string', 'max:20'],
                    'supplier_country' => ['required', 'not_in:0'],
                    'supplier_city'    => ['required', 'string', 'max:255'],
                    'supplier_street'  => ['required', 'string', 'max:255'],
                    'supplier_company' => ['required', 'not_in:0'],
                    'supplier_phone'   => ['required', 'string', 'max:20'],
                    'supplier_name'    => ['required', 'string', 'max:255']
                ]; 
            }
            default:break;
        }
    }
}
