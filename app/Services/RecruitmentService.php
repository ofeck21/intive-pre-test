<?php
namespace App\Services;

use App\Http\Resources\ReqruitmentResource;
use App\Models\Employees;
use App\Models\EmployeesAllDocuments;
use App\Models\EmployeesBankAccounts;
use App\Models\EmployeesEducationProfile;
use App\Models\EmployeesEmergencyContacts;
use App\Models\EmployeesFamilyStructure;
use App\Models\EmployeesImmigration;
use App\Models\EmployeesOnLeave;
use App\Models\EmployeesPhotos;
use App\Models\EmployeesWorkExperience;
use App\Models\Recruitment;
use App\Models\RecruitmentCertificate;
use App\Models\RecruitmentEmploymentHistory;
use App\Models\RecruitmentFamilyStructure;
use App\Models\RecruitmentFormalEducation;
use App\Models\RecruitmentIdentificationCard;
use App\Models\RecruitmentLanguage;
use App\Models\RecruitmentLeisureActivities;
use App\Models\RecruitmentPhoto;
use App\Models\RecruitmentReferensi;
use App\Models\RecruitmentSalary;
use App\Models\RecruitmentSocialActivities;
use App\Models\RecruitmentTraining;
use App\Models\User;
use App\ResponseServices\ResponseService;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Stmt\Return_;
use Yajra\DataTables\Facades\DataTables;

class RecruitmentService
{
    protected $recruitmentModel,
              $recruitmentIdentificationCardModel,
              $recruitmentRecruitmentFormalEducationModel,
              $recruitmentRecruitmentTrainingModel,
              $recruitmentCertificateModel,
              $recruitmentRecruitmentLanguage,
              $recruitmentSocialActivities,
              $recruitmentLeisureActivities,
              $recruitmentEmploymentHistory,
              $recruitmentPhoto,
              $employeeModel,
              $userModel,
              $recruitmentRecruitmentFamilyStructureModel;

    public function __construct() {
        $this->recruitmentModel = new Recruitment();
        $this->recruitmentIdentificationCardModel = new RecruitmentIdentificationCard();
        $this->recruitmentRecruitmentFamilyStructureModel = new RecruitmentFamilyStructure();
        $this->recruitmentRecruitmentFormalEducationModel = new RecruitmentFormalEducation();
        $this->recruitmentRecruitmentTrainingModel = new RecruitmentTraining();
        $this->recruitmentCertificateModel = new RecruitmentCertificate();
        $this->recruitmentRecruitmentLanguage = new RecruitmentLanguage();
        $this->recruitmentSocialActivities = new RecruitmentSocialActivities();
        $this->recruitmentLeisureActivities = new RecruitmentLeisureActivities();
        $this->recruitmentEmploymentHistory = new RecruitmentEmploymentHistory();
        $this->recruitmentPhoto = new RecruitmentPhoto();
        $this->employeeModel = new Employees();
        $this->userModel = new User();

    }

    public function getById($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (\Throwable $th) {
            return redirect('recruitments');
        }
        $data = New ReqruitmentResource($this->recruitmentModel->find($id));
        return ResponseService::toArray(json_encode($data));
    }

    public function getAll($request)
    {
        $data = $this->recruitmentModel->get();
        // return $data;
        // if ($request->dept) {
        //     if ($request->dept != 'all' && $request->dept != 0) {
        //         $data->where('department_id', $request->dept);
        //     }
        // }
        // if ($request->status) {
        //     if ($request->status != 'all' && $request->status != 0) {
        //         $data->where('employee_status_id', $request->status);
        //     }
        // }
        // $get = $data->get();
        return DataTables::of(
            $data
            // $get
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
            return $row->fullname;
            // if($row->company_id != null && $row->company_id != $user->company_id){
            //     $name .= $row->company != null ? " @ <span class=\"badge bg-success\">".$row->company->name ."</span>": '';
            // }
        })
        // ->editColumn('company', function($row){
        //     return $row->company->name;
        //     // if($row->company_id != null && $row->company_id != $user->company_id){
        //     //     $name .= $row->company != null ? " @ <span class=\"badge bg-success\">".$row->company->name ."</span>": '';
        //     // }
        // })

        ->editColumn('contact', function($row){
            return wa($row->mobile_phone_number);
            // if($row->company_id != null && $row->company_id != $user->company_id){
            //     $name .= $row->company != null ? " @ <span class=\"badge bg-success\">".$row->company->name ."</span>": '';
            // }
        })
        // ->editColumn('employee_status', function($row){
        //     return $row->employeesStatus->name;
        //     // if($row->company_id != null && $row->company_id != $user->company_id){
        //     //     $name .= $row->company != null ? " @ <span class=\"badge bg-success\">".$row->company->name ."</span>": '';
        //     // }
        // })

        ->editColumn('actions', function(){
            return [
                'show'      => true,
                'edit'      => true,
                'delete'    => true, 
                'print'     => true, 
            ];
            // return [
            //     'edit'      => ($user->hasRole('Super Admin') OR $user->hasPermissionTo('update department')) ? true : false,
            //     'delete'    => ($user->hasRole('Super Admin') OR $user->hasPermissionTo('delete department')) ? true : false, 
            // ];
        })
        ->addIndexColumn()
        ->rawColumns(['actions', 'name'])
        ->make();
    }

    public function store($request)
    {
        try {
            DB::beginTransaction();

            $payloadInsert = $this->recruitmentModel->payloadInsert($request);
            // $path = $request->file('photo')->store('public/recruitment/photo/3x4');
            // $payloadInsert['photo'] = $path;


            $nik = $request->file('file_nik')->store('public/recruitment/document');
            $payloadInsert['file_nik'] = $nik;
            $kk = $request->file('file_no_kk')->store('public/recruitment/document');
            $payloadInsert['file_no_kk'] = $kk;
            $skck = $request->file('file_no_skck')->store('public/recruitment/document');
            $payloadInsert['file_no_skck'] = $skck;

            $newData = $this->recruitmentModel->create($payloadInsert);
            if(!$newData) throw new Exception("Failed to create resource");
            
            
            $parent  = $newData;
            $idParen = $newData->id;

            $payloadInsertPhoto  = [];
            foreach ($request->file('photo') as $k => $v) {
                if ($v) {
                    $payloadInsertPhoto[] =[
                        'recruitment_id'   => $idParen,
                        'type'             => 'photo',
                        'path'             => $v->store('public/recruitment/photo/3x4'),
                    ]; 
                    
                }
            }
            

            if ($payloadInsertPhoto) {
                $newDataPhoto = $this->recruitmentPhoto->insert($payloadInsertPhoto);
                if(!$newDataPhoto) throw new Exception("Failed to create resource");
            }

            $payloadInsertIdentificationCard = [
                [
                    'recruitment_id'        => $idParen,
                    'card_number'           => $request->pasport_number,
                    'validity_period'       => $request->passport_validity,
                    'is_drivers_license'    => '0',
                    'type'                  => 'PASSPORT',
                ],
                [
                    'recruitment_id'        => $idParen,
                    'card_number'           => $request->identity_card_number_sim,
                    'validity_period'       => $request->validity_period_sim,
                    'is_drivers_license'    => '1',
                    'type'                  => $request->drivers_license,
                ],
                [
                    'recruitment_id'        => $idParen,
                    'card_number'           => $request->identity_card_number_bpjs_naker,
                    'validity_period'       => $request->validity_period_bpjs_naker,
                    'is_drivers_license'    => '0',
                    'type'                  => 'BPJS NAKER',
                ],
                [
                    'recruitment_id'        => $idParen,
                    'card_number'           => $request->identity_card_number_bpjs_kesehatan,
                    'validity_period'       => $request->validity_period_bpjs_kesehatan,
                    'is_drivers_license'    => '0',
                    'type'                  => 'BPJS KESEHATAN',
                ],
                [
                    'recruitment_id'        => $idParen,
                    'card_number'           => $request->identity_card_number_npwp,
                    'validity_period'       => $request->validity_period_npwp,
                    'is_drivers_license'    => '0',
                    'type'                  => 'NPWP',
                ],
            ];


            $identificationCard = $this->recruitmentIdentificationCardModel->insert($payloadInsertIdentificationCard);
            if(!$identificationCard) throw new Exception("Failed to create resource");


            $susunan_keluarga = [
                [
                    'structure'                     => 'father',
                    'name'                          => $request->father,
                    'gender'                        => $request->father_gender,
                    'age'                           => $request->father_age,
                    'education'                     => $request->father_education,
                    'position'                      => $request->father_position,
                    'company'                       => $request->father_company,
                ],
                [
                    'structure'                     => 'mother',
                    'name'                          => $request->mother,
                    'gender'                        => $request->mother_gender,
                    'age'                           => $request->mother_age,
                    'education'                     => $request->mother_education,
                    'position'                      => $request->mother_position,
                    'company'                       => $request->mother_company,
                ]
            ];

            if ($request->suami_istri) {
                $susunan_keluarga[] = [
                    'structure'                     => ($request->gender == 16) ? 'wife' : 'husband',
                    'name'                          => $request->suami_istri,
                    'gender'                        => ($request->gender == 16) ? '17' : '16',
                    'age'                           => $request->suami_istri_age,
                    'education'                     => $request->suami_istri_education,
                    'position'                      => $request->suami_istri_position,
                    'company'                       => $request->suami_istri_company,
                ];
            }
    
            foreach ($request->sibling_name as $key => $value) {
                $susunan_keluarga[] = [
                    'structure'         => 'sibling',
                    'name'              => $request->sibling_name[$key],
                    'gender'            => $request->sibling_gender[$key],
                    'age'               => $request->sibling_age[$key],
                    'education'         => $request->sibling_education[$key],
                    'position'          => $request->sibling_position[$key],
                    'company'           => $request->sibling_company[$key],
                ];
            }


            foreach ($request->child_name as $key => $value) {
                $susunan_keluarga[] = [
                    'structure'         => 'child',
                    'name'              => $request->child_name[$key],
                    'gender'            => $request->child_gender[$key],
                    'age'               => $request->child_age[$key],
                    'education'         => $request->child_education[$key],
                    'position'          => $request->child_position[$key],
                    'company'           => $request->child_company[$key],
                ];
            }


            foreach ($susunan_keluarga as $key => $value) {
                $payloadInsertFamily[$key]['structure']       = $value['structure'];
                $payloadInsertFamily[$key]['name']            = $value['name'];
                $payloadInsertFamily[$key]['gender']          = $value['gender'];
                $payloadInsertFamily[$key]['age']             = $value['age'];
                $payloadInsertFamily[$key]['education']       = $value['education'];
                $payloadInsertFamily[$key]['position']        = $value['position'];
                $payloadInsertFamily[$key]['company']         = $value['company'];
                $payloadInsertFamily[$key]['recruitment_id']  = $idParen;
            }

            if ($payloadInsertFamily) {
                $newDataFamily = $this->recruitmentRecruitmentFamilyStructureModel->insert($payloadInsertFamily);
                if(!$newDataFamily) throw new Exception("Failed to create resource");
            }


            if ($request->formal_education_nama_sekolah) {
                foreach ($request->formal_education_nama_sekolah as $key => $value) {
                    $payloadInsertFormalEducation[$key]['school_level']   = $request->formal_education_school_level[$key];
                    $payloadInsertFormalEducation[$key]['school_name']    = $request->formal_education_nama_sekolah[$key];
                    $payloadInsertFormalEducation[$key]['city']           = $request->formal_education_tempat_kota[$key];
                    $payloadInsertFormalEducation[$key]['start']          = $request->formal_education_mulai[$key];
                    $payloadInsertFormalEducation[$key]['finish']         = $request->formal_education_selesai[$key];
                    $payloadInsertFormalEducation[$key]['graduated']      = $request->formal_education_lulus[$key];
                    $payloadInsertFormalEducation[$key]['recruitment_id'] = $idParen;
                }
    
                if($payloadInsertFormalEducation){
                    $newDataFormalEducation = $this->recruitmentRecruitmentFormalEducationModel->insert($payloadInsertFormalEducation);
                    if(!$newDataFormalEducation) throw new Exception("Failed to create resource");
                }
            }

            
            if ($request->course_training_jenis) {
                $payloadInsertTraining = [];
                foreach ($request->course_training_jenis as $key => $value) {
                    $payloadInsertTraining[] = [
                        'field'                         => $request->course_training_jenis[$key],
                        'organizer'                     => $request->course_training_penyelenggara[$key],
                        'city'                          => $request->course_training_tempat[$key],
                        'times'                         => $request->course_training_waktu[$key],
                        'funded_by'                     => $request->course_training_dibiayai[$key],
                        'recruitment_id'                => $idParen
                    ];
                }
                if ($payloadInsertTraining) {
                    $newDataTraining = $this->recruitmentRecruitmentTrainingModel->insert($payloadInsertTraining);
                    if(!$newDataTraining) throw new Exception("Failed to create resource");
                }
            }

            if ($request->certificate_jenis) {
                $sertifikat = [];
                foreach ($request->certificate_jenis as $key => $value) {
                    $sertifikat[] = [
                        'recruitment_id'                 => $idParen,
                        'field'                          => $request->certificate_jenis[$key],
                        'organizer'                      => $request->certificate_penyelenggara[$key],
                        'city'                           => $request->certificate_tempat[$key],
                        'start'                          => $request->certificate_start[$key],
                        'finish'                         => $request->certificate_end[$key],
                        'funded_by'                      => $request->certificate_dibiayai[$key]
                    ];
    
                }
                if ($sertifikat) {
                    $newDataSertifikat = $this->recruitmentCertificateModel->insert($sertifikat);
                    if(!$newDataSertifikat) throw new Exception("Failed to create resource");
                }
            }


            if ($request->language_ability_hear) {
                $bahasa = [];
                foreach ($request->language_ability_hear as $key => $value) {
                    $bahasa[] = [
                        'recruitment_id'                     => $idParen,
                        'language'                           => $request->language_ability_bahasa[$key],
                        'hear'                               => $request->language_ability_hear[$key],
                        'read'                               => $request->language_ability_read[$key],
                        'write'                              => $request->language_ability_write[$key],
                        'speak'                              => $request->language_ability_speak[$key]
                    ];
    
                }
                if ($bahasa) {
                    $newDataBahasa = $this->recruitmentRecruitmentLanguage->insert($bahasa);
                    if(!$newDataBahasa) throw new Exception("Failed to create resource");
                }
            }

            if ($request->Social_Activities_social_activities) {
                $kegiatan_sosial = [];
                foreach ($request->Social_Activities_social_activities as $key => $value) {
                    $kegiatan_sosial[] = [
                        'recruitment_id'                     => $idParen,
                        'field'                              => $request->Social_Activities_social_activities[$key],
                        'organizer'                          => $request->Social_Activities_position[$key],
                        'city'                               => $request->Social_Activities_city[$key],
                        'start'                              => $request->Social_Activities_start[$key],
                        'finish'                             => $request->Social_Activities_end[$key]
                    ];
                }
    
                if ($kegiatan_sosial) {
                    $newDataSocial = $this->recruitmentSocialActivities->insert($kegiatan_sosial);
                    if(!$newDataSocial) throw new Exception("Failed to create resource");
                }
            }
    
            
            if ($request->Social_Activities_social_activities) {
                $social_activities = [];
                foreach ($request->Social_Activities_social_activities as $key => $value) {
                    $social_activities[] = [
                        'recruitment_id'                     => $idParen,
                        'leisure_activities'                 => $request->Leisure_Activities_Leisure_Activities[$key],
                        'active'                             => $request->Leisure_Activities_active[$key],
                        'passive'                            => $request->Leisure_Activities_passive[$key],
                    ];
    
                }
                if ($social_activities) {
                    $newDataSocialActive = $this->recruitmentLeisureActivities->insert($social_activities);
                    if(!$newDataSocialActive) throw new Exception("Failed to create resource");
                }
            }
            
            if ($request->employee_history_start_bln) {
                $Riwayat_Pekerjaan = [];
                foreach ($request->employee_history_start_bln as $key => $value) {
                    $Riwayat_Pekerjaan[] = [
                        'recruitment_id'                     => $idParen,
                        'start_month'                        => $request->employee_history_start_bln[$key],
                        'start_year'                         => $request->employee_history_start_thn[$key],
                        'start_salary'                       => $request->employee_history_start_gaji[$key],
                        'start_subsidy'                      => $request->employee_history_start_tunjangan[$key],
                        'start_position'                     => $request->employee_history_start_posisi_jabatan[$key],
                        'finish_month'                       => $request->employee_history_finish_bln[$key],
                        'finish_year'                        => $request->employee_history_finish_thn[$key],
                        'finish_salary'                      => $request->employee_history_finish_gaji[$key],
                        'finish_subsidy'                     => $request->employee_history_finish_tunjangan[$key],
                        'finish_position'                    => $request->employee_history_finish_posisi_jabatan[$key],
                        'company_name_and_address'           => $request->employee_history_company_name_and_address[$key],
                        'type_of_business'                   => $request->employee_history_jenis_usaha[$key],
                        'reason_to_stop'                     => $request->employee_history_alasan_berhenti[$key],
                        'brief_overview'                     => $request->employee_history_gambaran_singkat[$key],
                        'position_struktur_organisasi'       => $request->employee_history_gambaran_struktur_organisasi[$key],
                    ];
                }

                if ($Riwayat_Pekerjaan) {
                    $newDataPekerjaan = $this->recruitmentEmploymentHistory->insert($Riwayat_Pekerjaan);
                    if(!$newDataPekerjaan) throw new Exception("Failed to create resource");
                }
            }

            if ($request->gaji) {
                $payloadInsertSalary = [
                    'recruitment_id'    => $idParen,
                    'gaji'              => $request->gaji,
                    'question3'         => $request->question3,
                    'question4'         => $request->question4,
                ];
                $newDataSalary = RecruitmentSalary::create($payloadInsertSalary);
                if(!$newDataSalary) throw new Exception("Failed to create resource");
            }

            $payloadInsertRef = [
                [
                    'recruitment_id'        => $idParen,
                    'deskripsi'             => $request->ref_1_tab_6_des,
                    'hubungan'              => $request->ref_1_tab_6_hubungan,
                    'nama'                  => $request->ref_1_tab_6_nama,
                    'alamat'                => $request->ref_1_tab_6_alamat,
                    'telp'                  => $request->ref_1_tab_6_telp,
                    'pekerjaan_pendidikan'  => $request->ref_1_tab_6_pekerjaan_pendidikan,
                ],
                [
                    'recruitment_id'        => $idParen,
                    'deskripsi'             => $request->ref_2_tab_6_des,
                    'hubungan'              => $request->ref_2_tab_6_hubungan,
                    'nama'                  => $request->ref_2_tab_6_nama,
                    'alamat'                => $request->ref_2_tab_6_alamat,
                    'telp'                  => $request->ref_2_tab_6_telp,
                    'pekerjaan_pendidikan'  => $request->ref_2_tab_6_pekerjaan_pendidikan,
                ],
                [
                    'recruitment_id'        => $idParen,
                    'deskripsi'             => $request->ref_3_tab_6_des,
                    'hubungan'              => $request->ref_3_tab_6_nama,
                    'nama'                  => $request->ref_3_tab_6_alamat,
                    'alamat'                => $request->ref_3_tab_6_hubungan,
                    'telp'                  => $request->ref_3_tab_6_telp,
                    'pekerjaan_pendidikan'  => null,
                ],
                [
                    'recruitment_id'        => $idParen,
                    'deskripsi'             => $request->ref_4_tab_6_des,
                    'hubungan'              => $request->ref_4_tab_6_nama,
                    'nama'                  => $request->ref_4_tab_6_alamat,
                    'alamat'                => $request->ref_4_tab_6_hubungan,
                    'telp'                  => $request->ref_4_tab_6_telp,
                    'pekerjaan_pendidikan'  => null,
                ],
            ];
            $newDataRef = RecruitmentReferensi::insert($payloadInsertRef);
            if(!$newDataRef) throw new Exception("Failed to create resource");


            DB::commit();
            return ['status'     => true,
                    'messange'  => 'Succeess',
                    'data'      => $parent];
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['status' => false,
                    'messange'  => 'Error'];

        }

    }


    public function updateData($id,$request)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (\Throwable $th) {
            return redirect('recruitments');
        }
        $c = $this->recruitmentModel->find($id);
        $data = New ReqruitmentResource($c);

        if ($request->status == 'approve') {
            $ins = $this->insertEmployee($data);


            if ($ins['status'] == true) {
                $c->update(['status' =>  '1']);
                return [
                    'status'    => true,
                    'message'   => 'Yes, approve it!',
                    'data'      => $data
                ];
            }
            
            return [
                'status'    => false,
                'message'   => 'Request failed!',
                'data'      => $data
            ];

        }else{
            $del = $this->deleteEmployee($data);
            if ($del['status'] == true) {
                $c->update(['status' => '0']);
                return [
                    'status'    => true,
                    'message'   => 'Yes, reject it!',
                    'data'      => $data
                ];
            }
            return [
                'status'    => false,
                'message'   => 'Request failed!',
                'data'      => $del
            ];
        }

        return ResponseService::toArray(json_encode($data));
    }


    public function deleteData($id)
    {
        try {
            DB::beginTransaction();
            $id = Crypt::decryptString($id);
            $deleteRecruitment = $this->recruitmentModel->find($id);
            $deleteRecruitment->delete();
            
            $this->recruitmentPhoto->where('recruitment_id', $id)->delete();
            $this->recruitmentRecruitmentFamilyStructureModel->where('recruitment_id', $id)->delete();
            $this->recruitmentIdentificationCardModel->where('recruitment_id', $id)->delete();
            $this->recruitmentRecruitmentFormalEducationModel->where('recruitment_id', $id)->delete();
            $this->recruitmentRecruitmentTrainingModel->where('recruitment_id', $id)->delete();
            $this->recruitmentCertificateModel->where('recruitment_id', $id)->delete();
            $this->recruitmentRecruitmentLanguage->where('recruitment_id', $id)->delete();
            $this->recruitmentSocialActivities->where('recruitment_id', $id)->delete();
            $this->recruitmentLeisureActivities->where('recruitment_id', $id)->delete();
            $this->recruitmentEmploymentHistory->where('recruitment_id', $id)->delete();


            DB::commit();
            return ['status'     => true,
                    'messange'  => 'Succeess',
                    'data'      => $deleteRecruitment];
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['status' => false,
                    'messange'  => 'Error'];

        }
    }


    public function deleteEmployee($data)
    {
        try {
            DB::beginTransaction();
            $id = $data->id;
            $emp = $this->employeeModel->where('recruitment_id',$id)->first();
            EmployeesImmigration::where('employees_id', $emp->id)->delete();
            EmployeesOnLeave::where('employees_id', $emp->id)->delete();

            EmployeesFamilyStructure::where('employees_id', $emp->id)->delete();
            EmployeesEmergencyContacts::where('employees_id', $emp->id)->delete();
            EmployeesImmigration::where('employees_id', $emp->id)->delete();
            EmployeesBankAccounts::where('employees_id', $emp->id)->delete();
            EmployeesEducationProfile::where('employees_id', $emp->id)->delete();
            EmployeesAllDocuments::where('employees_id', $emp->id)->delete();
            EmployeesWorkExperience::where('employees_id', $emp->id)->delete();
            
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

    public function insertEmployee($emp)
    {
        request()->validate([
            'employee_id'                   => ['required', 'unique:employees,employee_id'],
            'id_card'                       => ['required', 'unique:employees,id_card'],
            'employee_id_number'            => ['required', 'unique:employees,employee_id_number'],
            'country_id'                    => ['required'],
            'province'                      => ['required'],
            'city'                          => ['required'],
            'zip_code'                      => ['required'],
            'job_level'                     => ['required'],
            'job_position_id'               => ['required'],
            'department_id'                 => ['required'],
            'company_id'                    => ['required'],
            'employee_work_status_id'       => ['required'],
            'employee_status_id'            => ['required'],
            'employment_status_id'          => ['required'],
            'employee_category_id'          => ['required'],
        ]);
        try {
            DB::beginTransaction();

            $payloadUser['name'] = $emp->first_name . ' ' . $emp->last_name;
            $payloadUser['email'] = $emp->email;
            $payloadUser['password'] = Hash::make($emp->nik);
        
            $user = $this->userModel->create($payloadUser);

            $userId = $user->id;
            // $userId = 40;

            $name = explode(' ', $emp->fullname);
            if (is_array($name)) {
                $first_name = $name['0'];
                $last_name  = str_replace($first_name.' ', '', $emp->fullname);
            }else{
                $first_name = $emp->fullname;
                $last_name  = $emp->fullname;
            }

            $payloadInsert['first_name']                    = $first_name;
            $payloadInsert['last_name']                     = $last_name;
            $payloadInsert['users_id']                      = $userId;
            $payloadInsert['employee_id']                   = request()->employee_id;
            $payloadInsert['id_card']                       = request()->id_card;
            $payloadInsert['national_number']               = $emp->nik;
            $payloadInsert['employee_id_number']            = request()->employee_id_number;
            $payloadInsert['mobile_phone']                  = $emp->mobile_phone_number;
            $payloadInsert['original_address']              = $emp->residence_address;
            $payloadInsert['country_id']                    = request()->country_id;
            $payloadInsert['province']                      = request()->province;
            $payloadInsert['city']                          = request()->city;
            $payloadInsert['zip_code']                      = request()->zip_code;
            $payloadInsert['date_of_birth']                 = $emp->date_of_birth;
            $payloadInsert['gender_id']                     = $emp->gender;
            $payloadInsert['marital_status_id']             = $emp->marital_status;
            $payloadInsert['job_levels']                    = request()->job_level;
            $payloadInsert['job_position_id']               = request()->job_position_id;
            $payloadInsert['department_id']                 = request()->department_id;
            $payloadInsert['company_id']                    = request()->company_id;
            $payloadInsert['employee_work_status_id']       = request()->employee_work_status_id;
            $payloadInsert['employee_status_id']            = request()->employee_status_id;
            $payloadInsert['employment_status_id']          = request()->employment_status_id;
            $payloadInsert['employee_category_id']          = request()->employee_category_id;
            $payloadInsert['tribes']                        = $emp->tribes;
            $payloadInsert['recruitment_id']                = $emp->id;

            $data = $this->employeeModel->create($payloadInsert);
            $photos = [];

            foreach ($emp->photos as $key => $value) {
                $photos[] = [
                    'employees_id'  => $data->id,
                    'path'          => $value->path,
                    'created_by'    => auth()->user()->id,
                    'updated_by'    => auth()->user()->id,
                ];
            }

             
            if ($photos) {
                $insertPhotos = EmployeesPhotos::insert($photos);
            }
            $family = [];
            foreach ($emp->family as $key => $value) {
                $structure = ['father' => 21, 'mother' => 22, 'sibling' => 26 ,'child' => 25, 'husband' => 23, 'wife' => 24];
                $family[] = [
                    'employees_id'  => $data->id,
                    'structure'     => $structure[$value->structure],
                    'is_bpjs'       => '0',
                    'name'          => $value->name,
                    'gender'        => $value->gender,
                    'age'           => $value->age,
                    'education'     => $value->education,
                    'position'      => $value->position,
                    'company'       => $value->company,
                    'created_by'    => auth()->user()->id,
                    'updated_by'    => auth()->user()->id,
                ];
            }

            if ($family) {
                $familyInsert = EmployeesFamilyStructure::insert($family);
            }

            $education = [];
            foreach ($emp->education as $key => $value) {
                $education[] = [
                    'employees_id'          => $data->id,
                    'school_type'           => 'formal',
                    'school_level'          => $value->school_level,
                    'school_name'           => $value->school_name,
                    'city'                  => $value->city,
                    'start'                 => $value->start,
                    'finish'                => $value->finish,
                    'graduated'             => $value->graduated,
                    'created_by'            => auth()->user()->id,
                    'updated_by'            => auth()->user()->id,
                ];
            }

            if ($education) {
                $educationInsert = EmployeesEducationProfile::insert($education);
            }

            $history = [];
            foreach ($emp->history as $key => $value) {
                $history[] = [
                    'employees_id'                      => $data->id,
                    'start_month'                       => $value->start_month,
                    'start_year'                        => $value->start_year,
                    'start_salary'                      => $value->start_salary,
                    'start_subsidy'                     => $value->start_subsidy,
                    'start_position'                    => $value->start_position,
                    'finish_month'                      => $value->finish_month,
                    'finish_year'                       => $value->finish_year,
                    'finish_salary'                     => $value->finish_salary,
                    'finish_subsidy'                    => $value->finish_subsidy,
                    'finish_position'                   => $value->finish_position,
                    'company_name_and_address'          => $value->company_name_and_address,
                    'type_of_business'                  => $value->type_of_business,
                    'reason_to_stop'                    => $value->reason_to_stop,
                    'brief_overview'                    => $value->brief_overview,
                    'position_struktur_organisasi'      => $value->position_struktur_organisasi,
                    'created_by'    => auth()->user()->id,
                    'updated_by'    => auth()->user()->id,
                ];
            }

            if ($history) {
                $historyInsert = EmployeesWorkExperience::insert($history);
            }

            DB::commit();
            return ['status'     => true,
                    'messange'  => 'Succeess',
                    'data'      => $data];
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['status' => false,
                    'messange'  => 'Error'];

        }

    }

}