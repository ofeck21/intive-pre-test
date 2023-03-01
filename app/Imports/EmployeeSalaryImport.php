<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Employees;
use App\Models\EmployeeSalary;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeSalaryImport implements ToModel, WithHeadingRow
{
    private $employee, $salary_id, $salary_name;

    public function __construct($salary_id, $salary_name) {
        $this->employee = Employees::pluck('id', 'employee_id_number');
        $this->salary_id = $salary_id;
        $this->salary_name = $salary_name;
    }

    public function model(array $row)
    {
        if(isset($this->employee[$row['nik']])){
            $month = Carbon::parse('01-'.date('m-Y'))->format('Y-m-d');
            EmployeeSalary::updateOrCreate(['month' => $month, 'employee_id' => $this->employee[$row['nik']]],[
                'salary_id'     => $this->salary_id,
                'employee_id'   => $this->employee[$row['nik']],
                'name'          => $this->salary_name,
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
