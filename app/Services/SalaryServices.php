<?php
namespace App\Services;

use App\Http\Resources\SalaryComponentsResource;
use App\Http\Resources\SalaryResource;
use App\Models\Salary;
use App\Models\SalaryComponent;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SalaryServices 
{
    protected $salaryModel;

    public function __construct() {
        $this->salaryModel = new Salary();
    }

    public function getAll()
    {
        try {
            $data = $this->salaryModel->get();
            // return EmployeesSalaryServices::collection($data)->toJson();
            return ResponseService::toArray(SalaryResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return ResponseService::toArray(SalaryResource::collection([])->toJson());
        }
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            $payloadInsert['employees_id']              = $id;
            $payloadInsert['school_type']               = $request->type; 
            $payloadInsert['school_level']              = $request->school_level; 
            $payloadInsert['school_name']               = $request->school_name; 
            $payloadInsert['city']                      = $request->city; 
            $payloadInsert['start']                     = $request->start; 
            $payloadInsert['finish']                    = $request->finish; 
            $payloadInsert['graduated']                 = $request->graduated; 
            $newData = $this->salaryModel->create($payloadInsert);
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
        $id = Crypt::decryptString($request->id);
        $checkData = $this->salaryModel->find($id_img);
        $roles = [];
        if ($checkData->phone_number!= $request->phone_number) $roles['phone_number'] = ['required','unique:employees_emergency_contacts,phone_number'];
        $request->validate($roles);
        try {
            DB::beginTransaction();
            
            
            $payloadInsert['employees_id']              = $id;
            $payloadInsert['status_family_stucture_id'] = $request->family_structure_status;
            $payloadInsert['name']                      = $request->name;
            $payloadInsert['phone_number']              = $request->phone_number;
            $payloadInsert['description']               = $request->description;
            

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
            
            $deleteData = $this->salaryModel->find($id_img);
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