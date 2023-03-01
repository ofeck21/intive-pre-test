<?php
namespace App\Services;

use App\Http\Resources\EmployeesAllDocumentsResource;
use App\Http\Resources\EmployeesBankAccountsResource;
use App\Models\EmployeesAllDocuments;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeesAllDocumentsServices 
{
    protected $employeesAllDocumentsModel;

    public function __construct() {
        $this->employeesAllDocumentsModel = new EmployeesAllDocuments();
    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $data = $this->employeesAllDocumentsModel->where('employees_id', $id)->get();
            return ResponseService::toArray(EmployeesAllDocumentsResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return ResponseService::toArray(EmployeesAllDocumentsResource::collection([])->toJson());
        }
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            $payloadInsert['employees_id']                      = $id;

            
            $payloadInsert['document_type_id']                    = $request->document_type_id;
            $payloadInsert['document_title']                      = $request->document_title;
            $payloadInsert['expiry_date']                         = $request->expiry_date;
            $payloadInsert['description']                         = $request->description;

            $uploadedFile = $request->file('document_file');
            $filename = time().$uploadedFile->getClientOriginalName();
            // return $filename;
            Storage::disk('local')->putFileAs(
                'employeeImmigrationFiles',
                $uploadedFile,
                $filename
            );
            $payloadInsert['document_file']     = $filename;
    
            $newData = $this->employeesAllDocumentsModel->create($payloadInsert);
            
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
        $checkData = $this->employeesAllDocumentsModel->find($id_img);
        try {
            DB::beginTransaction();
            
            
            $payloadInsert['employees_id']                      = $id;

            $payloadInsert['document_type_id']                    = $request->document_type_id;
            $payloadInsert['document_title']                      = $request->document_title;
            $payloadInsert['expiry_date']                         = $request->expiry_date;
            $payloadInsert['description']                         = $request->description;
            
            $uploadedFile = $request->file('document_file');
            $filename = time().$uploadedFile->getClientOriginalName();
            // return $filename;
            Storage::disk('local')->putFileAs(
                'employeeImmigrationFiles',
                $uploadedFile,
                $filename
            );
            $payloadInsert['document_file']     = $filename;

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
            
            $deleteData = $this->employeesAllDocumentsModel->find($id_img);
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