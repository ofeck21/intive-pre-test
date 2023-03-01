<?php
namespace App\Services;

use App\Http\Resources\EmployeesCategoryResource;
use App\Models\EmployeesCategory;

class EmployeesCategoryServices 
{
    protected $employeesCategoryModel;

    public function __construct() {
        $this->employeesCategoryModel = new EmployeesCategory();
    }

    public function getAll()
    {
        $data = $this->employeesCategoryModel->get();
        return EmployeesCategoryResource::collection($data);
    }
}