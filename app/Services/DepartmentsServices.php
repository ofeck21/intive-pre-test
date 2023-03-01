<?php
namespace App\Services;

use App\Http\Resources\CountriesResource;
use App\Models\Company;
use App\Models\Countries;
use App\Models\Department;

class DepartmentsServices 
{
    protected $departmentsModel;

    public function __construct() {
        $this->departmentsModel = new Department();
    }

    public function getAll()
    {
        $data = $this->departmentsModel->get();
        return CountriesResource::collection($data);
    }
}