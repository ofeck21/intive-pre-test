<?php
namespace App\Services;

use App\Models\Department;
use App\Models\JobPosition;
use App\Models\Salary;
use App\Models\SalaryComponent;
use App\Models\Shift;

class SelectService 
{
    public static function getEmployeeGroupingByJobPosistionFilterByDepartment($department_id)
    {
        $keyword = request()->q;
        $data = [];
        $positions = JobPosition::where('department_id', $department_id)->when($keyword!=null, function($q) use ($keyword){
            $q->whereLike('name', $keyword)
                ->orWhereHas('employees', function($qr) use ($keyword){
                $qr->whereLike('first_name', $keyword)
                    ->orWhereLike('last_name', $keyword);
            });
        })->paginate(10);
        
        foreach ($positions as $position) {
            $children = [];
            foreach($position->employees as $employee){
                $children[] = [
                    'id'    => $employee->id,
                    'text'  => $employee->first_name.' '.$employee->last_name
                ];
            }
            $data[] = [
                'text'  => $position->name,
                'children' => $children
            ];
        }
        
        return json_encode($data);
    }

    public static function getDepartmentByCompany($company_id)
    {
        $keyword = request()->q;
        $data = [];
        $deparments = Department::where('company_id', $company_id)->when($keyword!=null, function($q) use ($keyword){
            $q->whereLike('name', $keyword);
        })->paginate(10);
        
        foreach ($deparments as $deparment) {
            $data[] = [
                'id'    => $deparment->id,
                'text'  => $deparment->name
            ];
        }
        
        return json_encode($data);
    }

    public static function getShiftByCompany($company_id)
    {
        $keyword = request()->q;
        $data = [];
        $shifts = Shift::where('company_id', $company_id)->when($keyword!=null, function($q) use ($keyword){
            $q->whereLike('name', $keyword);
        })->paginate(10);
        
        foreach ($shifts as $shift) {
            $data[] = [
                'id'    => $shift->id,
                'text'  => $shift->name
            ];
        }
        
        return json_encode($data);
    }

    public static function getSalaryByCompany($company_id)
    {
        $keyword = request()->q;
        $data = [];
        $salaries = Salary::where('company_id', $company_id)->when($keyword!=null, function($q) use ($keyword){
            $q->whereLike('name', $keyword);
        })->paginate(10);
        
        foreach ($salaries as $salary) {
            $data[] = [
                'id'    => $salary->id,
                'text'  => $salary->name
            ];
        }
        
        return json_encode($data);
    }

    public static function getAllowanceByCompany($company_id)
    {
        $keyword = request()->q;
        $data = [];
        $allowances = SalaryComponent::where('company_id', $company_id)->where('type', 'allowance')->when($keyword!=null, function($q) use ($keyword){
            $q->whereLike('name', $keyword);
        })->paginate(10);
        
        foreach ($allowances as $allowance) {
            $data[] = [
                'id'    => $allowance->id,
                'text'  => $allowance->name
            ];
        }
        
        return json_encode($data);
    }
}