<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeesRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        if ($this->_method) {
            return [
                // 'employee_id'           => ['required', 'unique:employees,employee_id'],
                // 'id_card'               => ['required', 'unique:employees,id_card'],
                // 'national_number'       => ['required', 'unique:employees,national_number'],
                // 'email'                 => ['required', 'unique:users,email', 'email'],
                'first_name'            => ['required'],
                'last_name'             => ['required'],
                'username'              => ['required'],
                'contact_no'            => ['required'],
                'address'               => ['required'],
                'city'                  => ['required'],
                'province'              => ['required'],
                'zip_code'              => ['required'],
                'country'               => ['required'],
                'tribes'                => ['required'],
                'date_of_birth'         => ['required'],
            ];
        }
        return [
            'employee_id'           => ['required', 'unique:employees,employee_id'],
            'id_card'               => ['required', 'unique:employees,id_card'],
            'national_number'       => ['required', 'unique:employees,national_number'],
            'first_name'            => ['required'],
            'last_name'             => ['required'],
            'username'              => ['required'],
            'email'                 => ['required', 'unique:users,email', 'email'],
            'password'              => ['required'],
            'contact_no'            => ['required'],
            'address'               => ['required'],
            'city'                  => ['required'],
            'province'              => ['required'],
            'zip_code'              => ['required'],
            'country'               => ['required'],
            'tribes'                => ['required'],
            'date_of_birth'         => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'required'  => lang('The :attribute field is required'),
            'email'     => lang('The :attribute must be a valid email address'),
            'numeric'   => lang('The :attribute must be a number'),
            'unique'    => lang('The :attribute has already been taken')
        ];
    }
}
