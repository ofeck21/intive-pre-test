<?php
namespace App\Services;

use App\Http\Resources\EmployeesBankAccountsResource;
use App\Models\EmployeesBankAccounts;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EmployeesBankAccountsServices 
{
    protected $employeesBankAccountsModel;

    public function __construct() {
        $this->employeesBankAccountsModel = new EmployeesBankAccounts();
    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $data = $this->employeesBankAccountsModel->where('employees_id', $id)->get();
            return ResponseService::toArray(EmployeesBankAccountsResource::collection($data)->toJson());
        } catch (\Throwable $th) {
            return ResponseService::toArray(EmployeesBankAccountsResource::collection([])->toJson());
        }
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            
            $id = Crypt::decryptString($request->id);
            $payloadInsert['employees_id']                      = $id;

            $payloadInsert['account_title']                     = $request->account_title;
            $payloadInsert['account_number']                    = $request->account_number;
            $payloadInsert['bank_name']                         = $request->bank_name;
            $payloadInsert['bank_code']                         = $request->bank_code;
            $payloadInsert['bank_branch']                       = $request->bank_branch;
    
            $newData = $this->employeesBankAccountsModel->create($payloadInsert);
            
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
        $checkData = $this->employeesBankAccountsModel->find($id_img);
        try {
            DB::beginTransaction();
            
            
            $payloadInsert['employees_id']                      = $id;

            $payloadInsert['account_title']                     = $request->account_title;
            $payloadInsert['account_number']                    = $request->account_number;
            $payloadInsert['bank_name']                         = $request->bank_name;
            $payloadInsert['bank_code']                         = $request->bank_code;
            $payloadInsert['bank_branch']                       = $request->bank_branch;
            

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
            
            $deleteData = $this->employeesBankAccountsModel->find($id_img);
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