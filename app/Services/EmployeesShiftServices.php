<?php
namespace App\Services;

use App\Http\Resources\EmployeesShiftResource;
use App\Models\EmployeesShift;
use App\ResponseServices\ResponseService;
use Illuminate\Support\Facades\Crypt;

class EmployeesShiftServices 
{
    protected $employeesShiftModel;

    public function __construct() {
        $this->employeesShiftModel = new EmployeesShift();
    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (\Throwable $th) {
            return redirect('employee');
        }
        $data = $this->employeesShiftModel->where('employees_id', $id)->first();

        if (!$data) {
            return false;
        }

        $jsonData = json_encode(new EmployeesShiftResource($data));
        return ResponseService::toArray($jsonData);
    }


    public function insertData($request)
    {
        $request->validate(['shift_id' => 'required']);

        try {
            $id = Crypt::decryptString($request->id);
            $payloadInsert['shift_id']      = $request->shift_id;
            $payloadInsert['employees_id']  = $id;
            $payloadInsert['status']        = 'y';
            $c = $this->employeesShiftModel->where('employees_id', $id)->first();
            if ($c) {
                $c->update($payloadInsert);
                return $c;
            }
            return $this->employeesShiftModel->create($payloadInsert);
        } catch (\Throwable $th) {
            return redirect('employee');
        }
    }
}