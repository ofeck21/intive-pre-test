<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\Bpjs;
use Carbon\Carbon;
use App\Models\Employees;
use Illuminate\Bus\Queueable;
use App\Models\EmployeeSalary;
use App\Models\EmployeeSalaryComponent;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\PayrollJob;
use App\Models\Pph21;
use App\Models\Pph21Pkp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class RunPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $month, $company_id, $progress_id, $executed, $success, $failed, $remaining, $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($month, $company_id, $user = 'from terminal')
    {
        $this->month = '01-'.$month;
        $this->company_id = $company_id;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        info('Run Payroll', ['user' => $this->user]);
        // Getting Employees
        $employees = Employees::query()
                    ->with(['employeesStatus'])
                    ->whereHas('employeesStatus', function($q){
                        $q->where('name', 'Aktif');
                    });
        
        if($this->company_id != 'all'){
            $employees->where('company_id', $this->company_id);
        }

        // Define Data
        $this->remaining= $employees->count();
        $this->executed = 0;
        $this->success  = 0;
        $this->failed   = 0;
        
        // Generate Progress
        $progress = PayrollJob::create([
            'remaining' => $this->remaining,
            'success'   => $this->success,
            'failed'    => $this->failed,
            'executed'  => $this->executed,
            'company_id'=> $this->company_id == 'all' ? null : $this->company_id,
            'total'     => 0
        ]);

        $employees->chunk(200, function($employees) use ($progress){
            foreach ($employees as $employee) {
                $total_salary = 0;
                $total_allowance = 0;
                $total_reduction = 0;
                $attendances= $this->getAttendances($employee->id);
                $allowances = $this->getAllowances($employee->id);
                $reductions = $this->getReductions($employee->id);

                try {
                    // Get Salary
                    $salary = $this->getSalary($employee->id);
                    $total_salary += $salary->nominal;
                    
                    // calculate total allowance
                    $total_allowance = $this->calculateAllowance($allowances, $attendances);
                    $total_salary += $total_allowance;

                    // calculate total reductions
                    $total_reduction = $this->calculateReduction($reductions);
                    $total_salary -= $total_reduction;

                    // Calculate BPJS
                    $data_bpjs = (object) $this->calculateBPJS($employee->company_id, $salary->nominal, $allowances, $attendances);
                    $total_salary = $data_bpjs->netto - $total_reduction;

                    // Calculate Position Fee
                    $position_fee = $this->calculatePositionFee($total_salary);
                    $total_salary -= $position_fee;

                    // Calculate PPH21
                    $pph21 = $this->calculatePPh21($employee->id, $total_salary);
                    $total_salary -= $pph21;

                    // Store to DB
                    DB::beginTransaction();
                    $payroll = Payment::updateOrCreate(['date'=> Carbon::now('Asia/Jakarta')->format('Y-m').'-01', 'employee_id' => $employee->id],[
                        'date'          => Carbon::now('Asia/Jakarta')->format('Y-m').'-01',
                        'employee_id'   => $employee->id,
                        'company_id'    => $employee->company_id,
                        'nominal'       => $total_salary,
                        'payroll_job_id'=> $progress->id
                    ]);

                    // Store Salary to Payment Detail
                    PaymentDetail::updateOrCreate(['payment_id' => $payroll->id, 'code' => $salary->code],[
                        'payment_id'    => $payroll->id,
                        'code'          => $salary->code,
                        'nominal'       => $salary->nominal,
                        'type'          => 'salary',
                        'name'          => lang('Employees.Basic Salary')
                    ]);

                    // Store Allowances to Payment Detail
                    foreach ($allowances as $allowance) {
                        if($allowance->salary_component->given == 'daily'){
                            $nominal = $allowance->nominal * $attendances;
                        }else{
                            $nominal = $allowance->nominal;
                        }
                        PaymentDetail::updateOrCreate(['payment_id' => $payroll->id, 'code' => $allowance->salary_component->code],[
                            'payment_id'    => $payroll->id,
                            'code'          => $allowance->salary_component->code,
                            'name'          => $allowance->name,
                            'type'          => 'allowance',
                            'nominal'       => $nominal
                        ]);
                    }

                    // Store Reductions to Payment Detail
                    foreach ($reductions as $reduction) {
                        PaymentDetail::updateOrCreate(['payment_id' => $payroll->id, 'code' => $reduction->salary_component->code],[
                            'payment_id'    => $payroll->id,
                            'code'          => $reduction->salary_component->code,
                            'name'          => $reduction->name,
                            'type'          => 'reduction',
                            'nominal'       => $reduction->nominal
                        ]);
                    }

                    // Store BPJS to Payment Detail
                    foreach ($data_bpjs->bpjs as $bpjs) {
                        PaymentDetail::updateOrCreate(['payment_id' => $payroll->id, 'code' => $bpjs->code],[
                            'payment_id'    => $payroll->id,
                            'code'          => $bpjs->code,
                            'name'          => $bpjs->name,
                            'type'          => 'bpjs',
                            'nominal_company'    => $bpjs->nominal_company,
                            'nominal_employee'    => $bpjs->nominal_employee,
                            'company_percentage'    => $bpjs->company,
                            'employee_percentage'    => $bpjs->employee
                        ]);
                    }

                    // Store Position Fee to Payment Detail
                    PaymentDetail::updateOrCreate(['payment_id' => $payroll->id, 'code' => 'fee'],[
                        'payment_id'    => $payroll->id,
                        'code'          => 'fee',
                        'name'          => 'Biaya Jabatan',
                        'type'          => 'reduction',
                        'nominal'       => $position_fee
                    ]);

                    // Store PPh21 to Payment Detail
                    PaymentDetail::updateOrCreate(['payment_id' => $payroll->id, 'code' => 'pph21'],[
                        'payment_id'    => $payroll->id,
                        'code'          => 'pph21',
                        'name'          => 'PPh21',
                        'type'          => 'pph21',
                        'nominal'       => $pph21
                    ]);

                    DB::commit();
                    $this->executed++;
                    $this->remaining--;
                    $this->success++;
                    $this->updateProgress($progress->id);
                } catch (\Exception $e) {
                    Log::error('failed : '.$e->getMessage());
                    print_r('failed : '.$e->getMessage());
                    $this->remaining--;
                    $this->executed++;
                    $this->failed++;
                    $this->updateProgress($progress->id);
                }                    
            }
        });
        $this->updateProgress($progress->id, 'done');
        info('Payroll Finish');
    }

    public function getSalary($employee_id)
    {
        $salary = EmployeeSalary::where('employee_id', $employee_id)->whereDate('month', Carbon::parse($this->month)->format('Y-m-d'))->with('salary')->latest()->first();
        // if($salary == null){
        //     $salary = EmployeeSalary::where('employee_id', $employee_id)->orderBy('month', 'desc')->with('salary')->first();
        // }
        $response = (object) [
            'code'      => $salary->salary->code ?? '',
            'nominal'   => $salary->nominal ?? 0
        ];

        return $response;
    }

    public function getAllowances($employee_id)
    {
        return EmployeeSalaryComponent::where('employee_id', $employee_id)
                ->whereHas('salary_component', function($q){
                    $q->where('type', 'allowance');
                })
                ->with('salary_component', function($q){
                    $q->where('type', 'allowance');
                })
                ->whereDate('month', Carbon::parse($this->month)->format('Y-m-d'))->get();
    }

    public function calculateAllowance($allowances, $attendances)
    {
        $total_allowance = 0;

        foreach ($allowances as $allowance) {
            if($allowance->salary_component->given == 'daily'){
                $total_allowance += $allowance->nominal * $attendances;
            }else{
                $total_allowance += $allowance->nominal;
            }
        }

        return $total_allowance;
    }

    public function getReductions($employee_id)
    {
        return EmployeeSalaryComponent::where('employee_id', $employee_id)
                ->whereHas('salary_component', function($q){
                    $q->where('type', 'reduction');
                })
                ->whereDate('month', Carbon::parse($this->month)->format('Y-m-d'))->get();
    }

    public function calculateReduction($reductions)
    {
        $total_reduction = 0;

        foreach ($reductions as $reduction) {
            $total_reduction += $reduction->nominal;
        }

        return $total_reduction;
    }

    public function getAttendances($employee_id)
    {
        return Attendance::where('employee_id', $employee_id)->whereMonth('date', Carbon::parse($this->month)->format('m'))->where('rill', 1)->count();
    }

    public function createPaymentDetail($payment_id, $name, $nominal, $type)
    {
        PaymentDetail::create([
            'payment_id'    => $payment_id,
            'name'          => $name,
            'nominal'       => $nominal,
            'type'          => $type
        ]);
    }

    public function calculateBPJS($company_id, $salary, $allowances, $attendances)
    {
        $data_bpjs = Bpjs::where('company_id', $company_id)->get();

        $primary_allowance = 0;
        $total_allowance = 0;
        foreach ($allowances as $allowance) {
            if($allowance->salary_component->is_primary){
                if($allowance->salary_component->given == 'daily'){
                    $primary_allowance += $allowance->nominal * $attendances;
                }else{
                    $primary_allowance += $allowance->nominal;
                }
            }else{
                if($allowance->salary_component->given == 'daily'){
                    $total_allowance += $allowance->nominal * $attendances;
                }else{
                    $total_allowance += $allowance->nominal;
                }
            }
        }

        $total_salary = $salary + $primary_allowance;

        $bpjs_pt = 0;
        $bpjs_emp = 0;
        $data = [];
        foreach ($data_bpjs as $bpjs) {
            $bpjs_pt += ($total_salary * $bpjs->company) / 100;
            $bpjs_emp += ($total_salary * $bpjs->employee) / 100;

            $data['bpjs'][] = (object) [
                'code'      => $bpjs->code,
                'name'      => $bpjs->name,
                'employee'  => $bpjs->employee,
                'company'   => $bpjs->company,
                'nominal_employee'  => intval(($total_salary * $bpjs->employee) / 100),
                'nominal_company'  => intval(($total_salary * $bpjs->company) / 100),
            ];
        }

        $bruto = $total_salary + $bpjs_pt;

        $data['bruto'] = intval($bruto);
        $data['netto'] = intval($bruto - $bpjs_emp)+$total_allowance;
   
        return $data;
    }

    public function calculatePositionFee($salary)
    {
        $position_fee = ($salary * 5) / 100;
        return $position_fee > 500000 ? 500000 : intval($position_fee);
    }

    public function calculatePPh21($employee_id, $salary)
    {
        $employee = Employees::where('id', $employee_id)->with('pph21')->first();
        $join_date = $employee->date_of_joining;
        if($join_date == null){
            $working_time = 12;
        }else{
            $this_year = date('Y').'-12-31';
            $total_working_time = Carbon::parse($join_date)->diffInMonths($this_year);
            $working_time = ($total_working_time > 12) ? 12 : $total_working_time;
        }
        $yearly_salary = $salary * $working_time;
        $ptkp = $employee->pph21->ptkp ?? 0;
        $pkp = ($yearly_salary > $ptkp && $ptkp != 0) ? $yearly_salary - $ptkp : 0;
        // Pembulatan PKP Setahun
        $yearly_pkp = substr_replace(floor($pkp), '000',-3);
        // calculate PKP
        $pph21_pkp = Pph21Pkp::where('company_id', $employee->company_id)->get();
        $yearly_pph21 = 0;
        foreach ($pph21_pkp as $pph) {
            if($yearly_pkp >= $pph->from && $yearly_pkp <= $pph->until){
                $yearly_pph21 = ($yearly_pkp*$pph->rate)/100;
                break;
            }
        }
        $monthly_pph21 = $yearly_pph21/$working_time;
        $pph21 = 0;
        // Check employee have NPWP
        if($employee->npwp != null){
            $pph21 = ($monthly_pph21 * 100)/100;
        }else{
            $pph21 = ($monthly_pph21 * 120)/100;
        }

        return $pph21;
    }

    public function updateProgress($id, $status='process', $message="")
    {
        PayrollJob::where('id', $id)->update([
            'remaining' => $this->remaining,
            'success'   => $this->success,
            'failed'    => $this->failed,
            'executed'  => $this->executed,
            'status'    => $status,
            'message'   => $message
        ]);
    }
}
