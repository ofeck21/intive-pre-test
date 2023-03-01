<?php
namespace App\Services;

use App\Http\Resources\EmployeesWorkExperienceResource;
use App\Models\EmployeesWorkExperience;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeesWorkExperienceServices 
{
    protected $employeesWorkExperienceModel;

    public function __construct() {
        $this->employeesWorkExperienceModel = new EmployeesWorkExperience();
    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $data = $this->employeesWorkExperienceModel->where('employees_id', $id)->get();
            return ResponseService::toArray(EmployeesWorkExperienceResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return ResponseService::toArray(EmployeesWorkExperienceResource::collection([])->toJson());
        }
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            $payloadInsert['employees_id']                      = $id;
    
            $payloadInsert['start_month']                       = $request->start_month;
            $payloadInsert['start_year']                        = $request->start_year;
            $payloadInsert['start_salary']                      = $request->start_salary;
            $payloadInsert['start_subsidy']                     = $request->start_subsidy;
            $payloadInsert['start_position']                    = $request->start_position;
    
            $payloadInsert['finish_month']                      = $request->finish_month;
            $payloadInsert['finish_year']                       = $request->finish_year;
            $payloadInsert['finish_salary']                     = $request->finish_salary;
            $payloadInsert['finish_subsidy']                    = $request->finish_subsidy;
            $payloadInsert['finish_position']                   = $request->finish_position;
    
            $payloadInsert['company_name_and_address']          = $request->company_name_and_address;
            $payloadInsert['type_of_business']                  = $request->type_of_business;
    
            $payloadInsert['reason_to_stop']                    = $request->reason_to_stop;
            $payloadInsert['brief_overview']                    = $request->brief_overview;
            $payloadInsert['position_struktur_organisasi']      = $request->position_struktur_organisasi;
    
            $newData = $this->employeesWorkExperienceModel->create($payloadInsert);
            
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
        $checkData = $this->employeesWorkExperienceModel->find($id_img);
        try {
            DB::beginTransaction();
            
            
            $payloadInsert['employees_id']              = $id;

            $payloadInsert['start_month']                       = $request->start_month;
            $payloadInsert['start_year']                        = $request->start_year;
            $payloadInsert['start_salary']                      = $request->start_salary;
            $payloadInsert['start_subsidy']                     = $request->start_subsidy;
            $payloadInsert['start_position']                    = $request->start_position;

            $payloadInsert['finish_month']                      = $request->finish_month;
            $payloadInsert['finish_year']                       = $request->finish_year;
            $payloadInsert['finish_salary']                     = $request->finish_salary;
            $payloadInsert['finish_subsidy']                    = $request->finish_subsidy;
            $payloadInsert['finish_position']                   = $request->finish_position;

            $payloadInsert['company_name_and_address']          = $request->company_name_and_address;
            $payloadInsert['type_of_business']                  = $request->type_of_business;

            $payloadInsert['reason_to_stop']                    = $request->reason_to_stop;
            $payloadInsert['brief_overview']                    = $request->brief_overview;
            $payloadInsert['position_struktur_organisasi']      = $request->position_struktur_organisasi;
            

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
            
            $deleteData = $this->employeesWorkExperienceModel->find($id_img);
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