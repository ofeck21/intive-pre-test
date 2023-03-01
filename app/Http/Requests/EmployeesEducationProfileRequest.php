<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeesEducationProfileRequest extends FormRequest
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
        if ($this->type == 'formal') {
            $roles['school_level']          = ['required'];
        }
        $roles['school_name']               = ['required'];
        $roles['city']                      = ['required'];
        $roles['start']                     = ['required'];
        $roles['finish']                    = ['required'];
        $roles['graduated']                 = ['required'];

        return $roles;
    }
}
