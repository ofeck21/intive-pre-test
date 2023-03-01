<?php
namespace App\Services;

use App\Http\Resources\CountriesResource;
use App\Models\EmployeesOnLeave;
use Illuminate\Support\Facades\Crypt;

class EmployeesOnLeaveServices 
{
    protected $employeesOnLeaveModel;

    public function __construct() {
        $this->employeesOnLeaveModel = new EmployeesOnLeave();
    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $data = $this->employeesOnLeaveModel->where('employees_id', $id)->get();
            if ($data) {
                $total = [];
                foreach ($data as $key => $value) {
                    $total[] = $value->total_days;
                }
                return array_sum($total);
            }
            return 0;
        } catch (\Throwable $th) {
            return 0;
        }
    }
}