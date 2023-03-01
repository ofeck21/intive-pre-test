<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeesEmergencyContactsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return TRUE;
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
                'family_structure_status'           => ['required','numeric'],
                'name'                              => ['required'],
            ];
        }

        return [
            'family_structure_status'           => ['required','numeric'],
            'name'                              => ['required'],
            'phone_number'                      => ['required','unique:employees_emergency_contacts,phone_number'],
        ];
    }


    public function messages()
    {
        return [
            'required'  => lang('The :attribute field is required'),
            'email'     => lang('The :attribute must be a valid email address'),
            'numeric'   => lang('The :attribute must be a number'),
            'unique'    => lang('The :attribute has already been taken'),
            'max'       => lang('The :attribute must be :max kilobytes.')
        ];
    }
}
