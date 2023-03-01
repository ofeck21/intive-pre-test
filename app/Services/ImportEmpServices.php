<?php
namespace App\Services;

use App\Http\Resources\EmployeesResource;
use App\Models\Employees;
use App\Models\EmployeesAllDocuments;
use App\Models\EmployeesBankAccounts;
use App\Models\EmployeesCategory;
use App\Models\EmployeesEducationProfile;
use App\Models\EmployeesEmergencyContacts;
use App\Models\EmployeesFamilyStructure;
use App\Models\EmployeesImmigration;
use App\Models\EmployeesLeave;
use App\Models\EmployeesOnLeave;
use App\Models\EmployeesShift;
use App\Models\EmployeesStatus;
use App\Models\User;
use App\ResponseServices\ResponseService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Stmt\TryCatch;
use Yajra\DataTables\Facades\DataTables;

class ImportEmpServices
{
    protected $employeeModel;

    public function __construct() {
        $this->employeeModel = new Employees();
    }

    public function beforeImport($arr)
    {
        $excel = [];
        foreach ($arr[0] as $key => $value) {
            if ( $value[0] 
            &&   $value[1]
            &&   $value[2]
            &&   $value[3]
            &&   $value[4]
            &&   $value[5]
            &&   $value[6]
            &&   $value[7]
            &&   $value[8]
            &&   $value[9]
            &&   $value[10]
            &&   $value[11]
            &&   $value[12]
            &&   $value[13]
            &&   $value[14]
            &&   $value[15]
            &&   $value[16]
            &&   $value[17]
            &&   $value[18]
            &&   $value[19]
            &&   $value[20]
            &&   $value[21]
            &&   $value[22]
            &&   $value[23]
            &&   $value[24]
            &&   $value[25]
            &&   $value[26]
            ) {
                if ($key > 0) {
                    $excel[] = [
                        'employee_id'               => $value[0],
                        'id_card'                   => $value[1],
                        'national_number'           => $value[2],
                        'first_name'                => $value[3],
                        'last_name'                 => $value[4],
                        'username'                  => $value[5],
                        'email'                     => $value[6],
                        'password'                  => $value[7],
                        'contact_no'                => $value[8],
                        'address'                   => $value[9],
                        'city'                      => $value[10],
                        'province'                  => $value[11],
                        'zip_code'                  => $value[12],
                        'country'                   => $value[13],
                        'tribes'                    => $value[14],
                        'date_of_birth'             => $value[15],
                        'gender'                    => $value[16],
                        'marital_status'            => $value[17],
                        'company_id'                => $value[18],
                        'department_id'             => $value[19],
                        'job_position_id'           => $value[20],
                        'job_level_id'              => $value[21],
                        'employee_category_id'      => $value[22],
                        'employee_work_status_id'   => $value[23],
                        'employee_status_id'        => $value[24],
                        'employment_status_id'      => $value[25],
                        'employment_shift_id'       => $value[26],
                    ];
                }
            }
        }
        return $excel;
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            foreach ($request->username as $key => $value) {
                $dataUser = [
                    'name'              => $request->username[$key],
                    'email'             => $request->email[$key],
                    'password'          => Hash::make($request->password[$key]),
                    'company_id'        => $request->company_id[$key],
                ];
                $users = User::create($dataUser);
                if(!$users) throw new Exception("Failed to create resource");
                
                $payloadInsert['first_name']                    = $request->first_name[$key];
                $payloadInsert['last_name']                     = $request->last_name[$key];
                $payloadInsert['users_id']                      = $users->id;
                $payloadInsert['employee_id']                   = $request->employee_id[$key];
                $payloadInsert['id_card']                       = $request->id_card[$key];
                $payloadInsert['national_number']               = $request->national_number[$key];
                $payloadInsert['employee_id_number']            = $request->employee_id[$key];
                $payloadInsert['mobile_phone']                  = $request->contact_no[$key];
                $payloadInsert['original_address']              = $request->address[$key];
                $payloadInsert['country_id']                    = $request->country[$key];
                $payloadInsert['province']                      = $request->province[$key];
                $payloadInsert['city']                          = $request->city[$key];
                $payloadInsert['zip_code']                      = $request->zip_code[$key];
                $payloadInsert['date_of_birth']                 = $request->date_of_birth[$key];
                $payloadInsert['gender_id']                     = $request->gender[$key];
                $payloadInsert['marital_status_id']             = $request->marital_status[$key];
                $payloadInsert['job_levels']                    = $request->job_level_id[$key];
                $payloadInsert['job_position_id']               = $request->job_position_id[$key];
                $payloadInsert['department_id']                 = $request->department_id[$key];
                $payloadInsert['company_id']                    = $request->company_id[$key];
                $payloadInsert['employee_work_status_id']       = $request->employee_work_status_id[$key];
                $payloadInsert['employee_status_id']            = $request->employee_status_id[$key];
                $payloadInsert['employment_status_id']          = $request->employment_status_id[$key];
                $payloadInsert['employee_category_id']          = $request->employee_category_id[$key];
                $payloadInsert['tribes']                        = $request->tribes[$key];

    
                $newData = $this->employeeModel->create($payloadInsert);
                if(!$newData) throw new Exception("Failed to create resource");
                
                $payloadInsertShift['shift_id'] = $request->employment_shift_id[$key];
                $payloadInsertShift['employees_id'] = $newData->id;
                $payloadInsertShift['status'] = 'y';
                
                $shift = EmployeesShift::create($payloadInsertShift);
                if(!$shift) throw new Exception("Failed to create resource");
            }
            


            // return $shift;
            DB::commit();

            return ['status'    => true,
                    'message'   => 'Success',
                    'data'      => $newData];
        } catch (\Throwable $th) {
            DB::rollBack();

            return ['status'    => false,
                    'message'   => 'Error',
                    'data'      => null];
        }
    }

}