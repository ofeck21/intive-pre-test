<?php
namespace App\Services;

use App\Http\Resources\CountriesResource;
use App\Models\EmployeesLeave;
use Illuminate\Support\Facades\Crypt;

class EmployeesLeaveServices 
{
    protected $employeesLeaveModel;

    public function __construct() {
        $this->employeesLeaveModel = new EmployeesLeave();
    }

    public function getTotal($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $data = $this->employeesLeaveModel->where('year', date('Y'), 'employees_id', $id)->first();
            if ($data) {
                return $data->total;
            }
        } catch (\Throwable $th) {
            return 0;
        }
        return 0;
    }
}