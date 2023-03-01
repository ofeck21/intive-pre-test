<?php
namespace App\Services;

use App\Http\Resources\EmployeesSalaryResource;
use App\Models\EmployeeSalary;
use App\Models\Salary;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmployeesSalaryServices 
{
    protected $employeesSalaryModel;
    protected $salaryModel;

    public function __construct() {
        $this->employeesSalaryModel = new EmployeeSalary();
        $this->salaryModel = new Salary();
    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $data = $this->employeesSalaryModel->whereYear('month', date('Y'))->orderBy('month','asc')->where('employee_id', $id)->get();
            return ResponseService::toArray(EmployeesSalaryResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return ResponseService::toArray(EmployeesSalaryResource::collection([])->toJson());
        }
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);

            $salary = $this->salaryModel->find($request->salary_id);

            $payloadInsert['salary_id']                 = $request->salary_id; 
            $payloadInsert['employee_id']               = $id; 
            $payloadInsert['month']                     = $request->month; 
            $payloadInsert['name']                      = $salary->name; 
            $payloadInsert['nominal']                   = str_replace('.','',$request->nominal); 

            $newData = $this->employeesSalaryModel->where('month', $request->month)->first();
            if ($newData) {
                $newData->update($payloadInsert);
            } else {
                $newData = $this->employeesSalaryModel->create($payloadInsert);
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


    public function updateData($id, $id_img, $request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            $checkData = $this->employeesSalaryModel->find($id_img);
            
            $salary = $this->salaryModel->find($request->salary_id);

            $payloadInsert['salary_id']                 = $request->salary_id; 
            $payloadInsert['employee_id']               = $id; 
            $payloadInsert['month']                     = $request->month; 
            $payloadInsert['name']                      = $salary->name; 
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
            
            $deleteData = $this->employeesSalaryModel->find($id_img);
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