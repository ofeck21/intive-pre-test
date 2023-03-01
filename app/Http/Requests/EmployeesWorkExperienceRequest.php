<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeesWorkExperienceRequest extends FormRequest
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
        return [
            'start_month'                       => ['required'],
            'start_year'                        => ['required'],
            'start_salary'                      => ['required'],
            'start_subsidy'                     => ['required'],
            'start_position'                    => ['required'],

            'finish_month'                      => ['required'],
            'finish_year'                       => ['required'],
            'finish_salary'                     => ['required'],
            'finish_subsidy'                    => ['required'],
            'finish_position'                   => ['required'],

            'company_name_and_address'          => ['required'],
            'type_of_business'                  => ['required'],

            'reason_to_stop'                    => ['required'],
            'brief_overview'                    => ['required'],
            'position_struktur_organisasi'      => ['required'],
        ];
    }
}
