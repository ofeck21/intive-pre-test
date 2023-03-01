<?php
namespace App\Services;

use App\Http\Resources\CountriesResource;
use App\Http\Resources\EmployeesImmigrationResource;
use App\Models\EmployeesImmigration;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeesImmigrationsServices 
{
    protected $employeesImmigrationModel;

    public function __construct() {
        $this->employeesImmigrationModel = new EmployeesImmigration();
    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $data = $this->employeesImmigrationModel->where('employees_id', $id)->get();
            return ResponseService::toArray(EmployeesImmigrationResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return 0;
        }
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            $payloadInsert['employees_id']      = $id;
            $payloadInsert['document_type_id']  = $request->document_type_id;
            $payloadInsert['issue_date']        = $request->issue_date;
            $payloadInsert['country_id']        = $request->country_id;
            $payloadInsert['document_number']   = $request->document_number;
            $payloadInsert['expired_date']      = $request->expired_date;
            $payloadInsert['review_date']       = $request->eligible_review_date;
            


            $uploadedFile = $request->file('document_file');
            $filename = time().$uploadedFile->getClientOriginalName();
            // return $filename;
            Storage::disk('local')->putFileAs(
                'employeeImmigrationFiles',
                $uploadedFile,
                $filename
            );
            $payloadInsert['document_file']     = $filename;
            $newData = $this->employeesImmigrationModel->create($payloadInsert);
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
            $checkData = $this->employeesImmigrationModel->find($id_img);
            $payloadInsert['employees_id']      = $id;
            $payloadInsert['document_type_id']  = $request->document_type_id;
            $payloadInsert['issue_date']        = $request->issue_date;
            $payloadInsert['country_id']        = $request->country_id;
            $payloadInsert['document_number']   = $request->document_number;
            $payloadInsert['expired_date']      = $request->expired_date;
            $payloadInsert['review_date']       = $request->eligible_review_date;
            

            if($request->file){
                $uploadedFile = $request->file('document_file');
                $filename = time().$uploadedFile->getClientOriginalName();
                Storage::disk('local')->putFileAs(
                    'employeeImmigrationFiles',
                    $uploadedFile,
                    $filename
                );
                $payloadInsert['document_file']     = $filename;
            }
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
            
            $deleteData = $this->employeesImmigrationModel->find($id_img);
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