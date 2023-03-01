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
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Stmt\TryCatch;
use Yajra\DataTables\Facades\DataTables;

class EmployeesServices
{
    protected $employeeModel;

    public function __construct() {
        $this->employeeModel = new Employees();
    }

    public function getAll($request)
    {
        // return $_GET;
        $user = Auth::user();
        $data = $this->employeeModel->with('company');
        if ($request->dept) {
            if ($request->dept != 'all' && $request->dept != 0) {
                $data->where('department_id', $request->dept);
            }
        }
        if ($request->status) {
            if ($request->status != 'all' && $request->status != 0) {
                $data->where('employee_status_id', $request->status);
            }
        }
        $get = $data->get();
        return DataTables::of(
            $get
            // search[value]: test
            // Department::query()->with('company')
            // ->when(!$user->hasRole('Super Admin'), function($q) use ($user){
            //     $q->where('company_id', $user->company_id);
            // })
        )
        ->editColumn('id', function($row){
            return Crypt::encryptString($row->id);
            // if($row->company_id != null && $row->company_id != $user->company_id){
            //     $name .= $row->company != null ? " @ <span class=\"badge bg-success\">".$row->company->name ."</span>": '';
            // }
        })
        // ->filter(function($query) use ($request){
        //     if (!empty($request->search['value'])) {
        //         $query->search($request->search['value']);
        //     }
        // })
        ->editColumn('name', function($row){
            return $row->first_name." ".$row->last_name;
            // if($row->company_id != null && $row->company_id != $user->company_id){
            //     $name .= $row->company != null ? " @ <span class=\"badge bg-success\">".$row->company->name ."</span>": '';
            // }
        })
        ->editColumn('company', function($row){
            return $row->company->name ?? '';
            // if($row->company_id != null && $row->company_id != $user->company_id){
            //     $name .= $row->company != null ? " @ <span class=\"badge bg-success\">".$row->company->name ."</span>": '';
            // }
        })

        ->editColumn('contact', function($row){
            return wa($row->mobile_phone);
            // if($row->company_id != null && $row->company_id != $user->company_id){
            //     $name .= $row->company != null ? " @ <span class=\"badge bg-success\">".$row->company->name ."</span>": '';
            // }
        })
        ->editColumn('employee_status', function($row){
            return $row->employeesStatus->name ?? '';
            // if($row->company_id != null && $row->company_id != $user->company_id){
            //     $name .= $row->company != null ? " @ <span class=\"badge bg-success\">".$row->company->name ."</span>": '';
            // }
        })

        ->editColumn('actions', function() use ($user){
            return [
                'show'      => true,
                'edit'      => true,
                'delete'    => true, 
            ];
            // return [
            //     'edit'      => ($user->hasRole('Super Admin') OR $user->hasPermissionTo('update department')) ? true : false,
            //     'delete'    => ($user->hasRole('Super Admin') OR $user->hasPermissionTo('delete department')) ? true : false, 
            // ];
        })
        ->addIndexColumn()
        ->rawColumns(['actions', 'name'])
        ->make();

        // return ;
    }


    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (\Throwable $th) {
            return redirect('employee');
        }
        return New EmployeesResource($this->employeeModel->find($id));
    }

    public function insertData($request)
    {
        try {
            DB::beginTransaction();
            $dataUser = [
                'name'  => $request->username,
                'email' => $request->email,
                'password'  => Hash::make($request->password),
            ];
            $users = User::create($dataUser);
            
            $payloadInsert = $this->employeeModel->payloadInsert($request);
            $payloadInsert['users_id'] = $users->id;

            // return $payloadInsert;

            $newData = $this->employeeModel->create($payloadInsert);
            if(!$newData) throw new Exception("Failed to create resource");

            // $payloadInsertShift['shift_id'] = $request->employment_shift_id;
            $payloadInsertShift['shift_id'] = $request->employment_shift_id;
            $payloadInsertShift['employees_id'] = $newData->id;
            $payloadInsertShift['status'] = 'y';
            
            $shift = EmployeesShift::updateOrCreate($payloadInsertShift);
            if(!$shift) throw new Exception("Failed to create resource");

            // return $shift;
            DB::commit();

            return ['status'    => true,
                    'message'   => 'Success',
                    'data'      => $newData];
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }

    public function updatetData($id, $request)
    {
        $id = Crypt::decryptString($id);
        $emp = $this->employeeModel->find($id);
        $roles = [];
        if ($emp->users->email != $request->email) $roles['email'] = ['required', 'unique:users,email', 'email'];
        if ($emp->employee_id != $request->employee_id) $roles['employee_id'] = ['required', 'unique:employees,employee_id'];
        if ($emp->id_card != $request->id_card) $roles['id_card'] = ['required', 'unique:employees,id_card'];
        if ($emp->national_number != $request->national_number) $roles['national_number'] = ['required', 'unique:employees,national_number'];
        $request->validate($roles);

        try {
            DB::beginTransaction();
            $dataUser = [
                'name'  => $request->username,
                'email' => $request->email,
            ];
            if ($request->password) {
                $dataUser['password'] = Hash::make($request->password); 
            }
            User::find($emp->users_id)->updated($dataUser);
            $payloadUpdate = $this->employeeModel->payloadUpdate($request);
            $payloadUpdate['users_id'] = $emp->users_id;
            $update = $emp->update($payloadUpdate);

            if(!$update ) throw new Exception("Failed to create resource");

            DB::commit();

            return ['status'    => true,
                    'message'   => 'Success',
                    'data'      => $emp];
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }

    public function deleteData($id)
    {
        try {
            DB::beginTransaction();
            $id = Crypt::decryptString($id);
            $emp = $this->employeeModel->find($id);
            EmployeesImmigration::where('employees_id', $emp->id)->delete();
            EmployeesOnLeave::where('employees_id', $emp->id)->delete();

            EmployeesFamilyStructure::where('employees_id', $emp->id)->delete();
            EmployeesEmergencyContacts::where('employees_id', $emp->id)->delete();
            EmployeesImmigration::where('employees_id', $emp->id)->delete();
            EmployeesBankAccounts::where('employees_id', $emp->id)->delete();
            EmployeesEducationProfile::where('employees_id', $emp->id)->delete();
            EmployeesAllDocuments::where('employees_id', $emp->id)->delete();

            User::find($emp->users_id)->delete();
            $emp->delete();
            DB::commit();
            if ($emp) {
                return ['status'    => true,
                        'message'   => 'Success',
                        'data'      => $emp];
            }else{
                return ['status'    => false,
                        'message'   => 'Error',
                        'data'      => $emp];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['status'    => false,
                    'message'   => 'Error',
                    'data'      => null];
        }
        
    }
}