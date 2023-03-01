<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Employees;
use App\Models\EmployeeSalaryComponent;
use App\Models\SalaryComponent;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalaryComponentImport implements ToModel, WithHeadingRow
{
    private $employee, $salaryComponent;

    public function __construct() {
        $this->employee = Employees::pluck('id', 'employee_id_number');
        $this->salaryComponent   = SalaryComponent::pluck('id', 'code');
    }

    public function model(array $row)
    {
        if(isset($this->employee[$row['nik']]) && isset($this->salaryComponent[$row['kode']])){
            $month = Carbon::parse('01-'.date('m-Y'))->format('Y-m-d');
            EmployeeSalaryComponent::updateOrCreate(['month' => $month, 'employee_id' => $this->employee[$row['nik']], 'salary_component_id' => $this->salaryComponent[$row['kode']]],[
                'salary_component_id'     => $this->salaryComponent[$row['kode']],
                'employee_id'   => $this->employee[$row['nik']],
                'name'          => SalaryComponent::where('code', $row['kode'])->first()->name,
                'month'         => $month,
                'nominal'       => $row['nominal']
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'kode'      => 'required',
            'nik'       => 'required',
            'nominal'   => 'required'
        ];
    }
}
