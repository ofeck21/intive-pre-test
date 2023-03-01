<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\Employees;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ImportAttendance implements ToModel, WithHeadingRow
{
    use Importable;

    private $company_id, $employees;

    public function __construct($company_id) {
        $this->company_id = $company_id;
        $this->employees = Employees::where('company_id', $company_id)->pluck('id', 'employee_id_number');
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if(isset($this->employees[$row['nik']])){
           Attendance::updateOrCreate(['nik'=>$row['nik'],'date' => Carbon::parse($row['tanggal'])->format('Y-m-d')],[
                'employee_id'   => $this->employees[$row['nik']],
                'company_id'    => $this->company_id,
                'nik'           => $row['nik'],
                'date'          => Carbon::parse($row['tanggal'])->format('Y-m-d'),
                'clock_in'      => $row['scan_masuk'],
                'clock_out'     => $row['scan_pulang'],
                'working_type'  => $row['jam_kerja'],
                'rill'          => $row['riil'],
                'late'          => $row['terlambat'],
                'early'         => $row['plg_cepat'],
                'overtime'      => $row['lembur'],
                'working_hours' => $row['jml_kehadiran'],
                'exception'     => $row['pengecualian'],
                'symbol'        => $row['simbol'],
                'normal_day'    => $row['hari_normal'],
                'week'          => $row['week'],
                'sum_worktime'  => $row['sumworktime'],
                'sum_overtime'   => $row['sumovertime']
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'tanggal'      => 'required',
            'nik'       => 'required'
        ];
    }
}
