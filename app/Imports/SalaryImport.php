<?php

namespace App\Imports;

use App\Models\Employees;
use App\Models\EmployeeSalary;
use App\Models\Salary;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalaryImport implements ToModel, WithHeadingRow
{
    private $employee, $salary;

    public function __construct() {
        $this->employee = Employees::pluck('id', 'employee_id_number');
        $this->salary   = Salary::pluck('id', 'code');
    }

    public function model(array $row)
    {
        if(isset($this->employee[$row['nik']]) && isset($this->salary[$row['kode_gaji']])){
            $month = Carbon::parse('01-'.date('m-Y'))->format('Y-m-d');
            EmployeeSalary::updateOrCreate(['month' => $month, 'employee_id' => $this->employee[$row['nik']], 'salary_id' => $this->salary[$row['kode_gaji']]],[
                'salary_id'     => $this->salary[$row['kode_gaji']],
                'employee_id'   => $this->employee[$row['nik']],
                'name'          => Salary::where('code', $row['kode_gaji'])->first()->name,
                'month'         => $month,
                'nominal'       => $row['nominal']
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'kode_gaji' => 'required',
            'nik'       => 'required',
            'nominal'   => 'required'
        ];
    }

}
