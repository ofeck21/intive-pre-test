<?php
namespace App\Services;

use App\Http\Resources\SalaryComponentsResource;
use App\Models\SalaryComponent;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SalaryComponentsServices 
{
    protected $salaryComponentsModel;

    public function __construct() {
        $this->salaryComponentsModel = new SalaryComponent();
    }

    public function getById($components)
    {
        try {
            $data = $this->salaryComponentsModel->where('code', $components)->first();
            return $data;
            // return EmployeesSalaryServices::collection($data)->toJson();
            return ResponseService::toArray(SalaryComponentsResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return ResponseService::toArray(SalaryComponentsResource::collection([])->toJson());
        }
    }
    
    public function getAll()
    {
        try {
            $data = $this->salaryComponentsModel->get();
            return $data;
            // return EmployeesSalaryServices::collection($data)->toJson();
            return ResponseService::toArray(SalaryComponentsResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return ResponseService::toArray(SalaryComponentsResource::collection([])->toJson());
        }
    }

}