<?php
namespace App\Services;

use App\Http\Resources\EmployeesSocialProfileResource;
use App\Models\EmployeesSocialProfile;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmployeesSocialProfileServices 
{
    protected $employeesSocialProfileModel;

    public function __construct() {
        $this->employeesSocialProfileModel = new EmployeesSocialProfile();
    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (\Throwable $th) {
            return redirect('employee');
        }

        $data = $this->employeesSocialProfileModel->where('employees_id', $id)->get();
        return ResponseService::toArray(EmployeesSocialProfileResource::collection($data)->toJson());
        // return New EmployeesSocialProfileResource($this->employeesSocialProfileModel->where('employees_id',$id)->first());
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            
            $payloadInsert['employees_id']          = $id;
            $payloadInsert['social_name']           = $request->social_name;
            $payloadInsert['social_id']             = $request->social_id;
            $payloadInsert['social_link']           = $request->social_link;
            $c = $this->employeesSocialProfileModel->create($payloadInsert);

            DB::commit();

            return ['status'    => true,
                    'message'   => 'Success',
                    'data'      => $c];
        } catch (\Throwable $th) {
            return ['status'    => false,
                    'message'   => 'Error',
                    'data'      => null];
        }
    }


    public function updateData($id_s, $id, $request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            
            $payloadUpdate['employees_id']          = $id;
            $payloadUpdate['social_name']           = $request->social_name;
            $payloadUpdate['social_id']             = $request->social_id;
            $payloadUpdate['social_link']           = $request->social_link;
            $c = $this->employeesSocialProfileModel->find($id);
            $c->update($payloadUpdate);
    
            DB::commit();

            return ['status'    => true,
                    'message'   => 'Success',
                    'data'      => $c];
        } catch (\Throwable $th) {
            return ['status'    => false,
                    'message'   => 'Error',
                    'data'      => null];
        }
    }

    public function deleteData($id_img)
    {
        try {
            DB::beginTransaction();
            
            $deleteData = $this->employeesSocialProfileModel->find($id_img);
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