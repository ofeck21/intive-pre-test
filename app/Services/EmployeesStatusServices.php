<?php
namespace App\Services;

use App\Http\Resources\CountriesResource;
use App\Models\EmployeesStatus;
use App\Models\JobPosition;

class EmployeesStatusServices 
{
    protected $employeesStatusModel;

    public function __construct() {
        $this->employeesStatusModel = new EmployeesStatus();
    }

    public function getAll()
    {
        $data = $this->employeesStatusModel->get();
        return CountriesResource::collection($data);
    }
}