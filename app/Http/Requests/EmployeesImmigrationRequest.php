<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class EmployeesImmigrationRequest extends FormRequest
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
                'document_type_id'              => ['required'],
                'document_number'               => ['required','numeric'],
                'issue_date'                    => ['required'],
                'country_id'                    => ['required'],
                'document_file'                 => ['max:2000'],
            ];
        }
        
        return [
            'document_type_id'              => ['required'],
            'document_number'               => ['required','numeric'],
            'issue_date'                    => ['required'],
            'country_id'                    => ['required'],
            'document_file'                 => ['required','max:2000'],
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
