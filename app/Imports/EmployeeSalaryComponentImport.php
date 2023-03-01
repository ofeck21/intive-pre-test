<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Employees;
use App\Models\EmployeeSalaryComponent;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeSalaryComponentImport implements ToModel, WithHeadingRow
{
    private $employee, $salary_component_id, $salary_component_name;

    public function __construct($salary_component_id, $salary_component_name) {
        $this->employee = Employees::pluck('id', 'employee_id_number');
        $this->salary_component_id = $salary_component_id;
        $this->salary_component_name = $salary_component_name;
    }

    public function model(array $row)
    {
        if(isset($this->employee[$row['nik']])){
            $month = Carbon::parse('01-'.date('m-Y'))->format('Y-m-d');
            EmployeeSalaryComponent::updateOrCreate(['month' => $month, 'employee_id' => $this->employee[$row['nik']]],[
                'salary_component_id'     => $this->salary_component_id,
                'employee_id'   => $this->employee[$row['nik']],
                'name'          => $this->salary_component_name,
                'month'         => $month,
                'nominal'       => $row['nominal']
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nik'       => 'required',
            'nominal'   => 'required'
        ];
    }
}
