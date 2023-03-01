<?php
namespace App\Services;

use App\Http\Resources\EmployeesFamilyStructureResource;
use App\Models\EmployeesFamilyStructure;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmployeesFamilyStructureServices 
{
    protected $employeesFamilyStructureModel;

    public function __construct() {
        $this->employeesFamilyStructureModel = new EmployeesFamilyStructure();
    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $data = $this->employeesFamilyStructureModel->where('employees_id', $id)->get();
            // return $data;
            
            // return EmployeesFamilyStructureResource::collection($data);
            return ResponseService::toArray(EmployeesFamilyStructureResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return ResponseService::toArray(EmployeesFamilyStructureResource::collection([])->toJson());
        }
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            $payloadInsert['employees_id']                      = $id;

            $payloadInsert['structure']             = $request->family_structure_status;
            $payloadInsert['is_bpjs']               = ($request->is_bpjs)?1:0;
            $payloadInsert['name']                  = $request->name;
            $payloadInsert['gender']                = $request->gender;
            $payloadInsert['age']                   = $request->age;
            $payloadInsert['education']             = $request->education;
            $payloadInsert['position']              = $request->position;
            $payloadInsert['company']               = $request->company;

    
            $newData = $this->employeesFamilyStructureModel->create($payloadInsert);
            
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
        $checkData = $this->employeesFamilyStructureModel->find($id_img);
        try {
            DB::beginTransaction();
            
            $payloadInsert['employees_id']              = $id;

            $payloadInsert['structure']             = $request->family_structure_status;
            $payloadInsert['is_bpjs']               = ($request->is_bpjs == "on") ? '1' : '0';
            $payloadInsert['name']                  = $request->name;
            $payloadInsert['gender']                = $request->gender;
            $payloadInsert['age']                   = $request->age;
            $payloadInsert['education']             = $request->education;
            $payloadInsert['position']              = $request->position;
            $payloadInsert['company']               = $request->company;
            

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
            
            $deleteData = $this->employeesFamilyStructureModel->find($id_img);
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