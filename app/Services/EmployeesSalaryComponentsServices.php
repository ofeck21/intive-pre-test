<?php
namespace App\Services;

use App\Http\Resources\EmployeesSalaryComponentsResource;
use App\Models\EmployeeSalaryComponent;
use App\Models\Salary;
use App\Models\SalaryComponent;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmployeesSalaryComponentsServices 
{
    protected $employeesSalaryComponentsModel;
    protected $salaryComponentsModel;
    protected $salaryModel;

    public function __construct() {
        $this->employeesSalaryComponentsModel = new EmployeeSalaryComponent();
        $this->salaryComponentsModel = new SalaryComponent();
        $this->salaryModel = new Salary();
    }

    public function getById($id, $components)
    {
        try {
            $id = Crypt::decryptString($id);

            $components = $this->salaryComponentsModel->where('code', $components)->first();
            // return $components;

            $data = $this->employeesSalaryComponentsModel->where('salary_component_id', $components->id)->whereYear('month', date('Y'))->orderBy('month','asc')->where('employee_id', $id)->get();
            return ResponseService::toArray(EmployeesSalaryComponentsResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return ResponseService::toArray(EmployeesSalaryComponentsResource::collection([])->toJson());
        }
    }

    public function insertData($request, $id, $code)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);

            $salaryComponents = $this->salaryComponentsModel->where('code',$code)->first();

            $payloadInsert['salary_component_id']       = $salaryComponents->id; 
            $payloadInsert['employee_id']               = $id; 
            $payloadInsert['month']                     = $request->month; 
            $payloadInsert['name']                      = $salaryComponents->name; 
            $payloadInsert['nominal']                   = str_replace('.','',$request->nominal); 

            $newData = $this->employeesSalaryComponentsModel->where('month', $request->month)->first();
            if ($newData) {
                $newData->update($payloadInsert);
            } else {
                $newData = $this->employeesSalaryComponentsModel->create($payloadInsert);
            }
            
            DB::commit();

            return ['status'    => true,
                    'message'   => 'Success',
                    'data'      => $newData];
        } catch (\Throwable $th) {
            return ['status'    => false,
                    'message'   => 'Error',
                    'data'      => null];
        }
    }


    public function updateData($id, $code, $id_data, $request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            $checkData = $this->employeesSalaryComponentsModel->find($id_data);
            $salaryComponents = $this->salaryComponentsModel->where('code', $code)->first();

            $payloadInsert['salary_component_id']       = $salaryComponents->id; 
            $payloadInsert['employee_id']               = $id; 
            $payloadInsert['month']                     = $request->month; 
            $payloadInsert['name']                      = $salaryComponents->name; 
            $payloadInsert['nominal']                   = str_replace('.','',$request->nominal); 
            
            $checkData->update($payloadInsert);
            DB::commit();

            return ['status'    => true,
                    'message'   => 'Success',
                    'data'      => $checkData];
        } catch (\Throwable $th) {
            return ['status'    => false,
                    'message'   => 'Error',
                    'data'      => null];
        }
    }


    public function deleteData($id, $id_img)
    {
        try {
            DB::beginTransaction();
            
            $deleteData = $this->employeesSalaryComponentsModel->find($id_img);
            $deleteData->delete();
            DB::commit();

            return ['status'    => true,
                    'message'   => 'Success',
                    'data'      => $deleteData];
        } catch (\Throwable $th) {
            return ['status'    => false,
                    'message'   => 'Error',
                    'data'      => null];
        }
    }
}