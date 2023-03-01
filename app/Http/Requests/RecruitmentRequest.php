<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class RecruitmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        // return [];
        if ($this->step == 1) {
            $roles = [
                'photo'                 => ['required'],
                'photo.*'               => ['mimes:jpeg,jpg,png,gif','max:20000'],
                'full_name'             => ['required'],
                'posisi_yang_dilamar'   => ['required'],
                'nik'                   => ['required','numeric', 'unique:recruitment,nik', 'unique:employees,national_number'],
                'file_nik'              => ['required'],
                'no_kk'                 => ['required', 'numeric', 'unique:recruitment,no_kk',],
                'file_no_kk'            => ['required'],
                'no_skck'               => ['required', 'numeric', 'unique:recruitment,no_skck',],
                'file_no_skck'          => ['required'],
                'place_of_birth'        => ['required'], 
                'date_of_birth'         => ['required', 'date'], 
                'gender'                => ['required'],
                'mobile_phone_number'   => ['required', 'numeric', 'unique:recruitment,mobile_phone_number', 'unique:employees,mobile_phone'], 
                'phone_number'          => ['numeric'], 
                'email'                 => ['required', 'email', 'unique:recruitment,email', 'unique:users,email'], 
                'id_card_address'       => ['required'], 
                'residence_address'     => ['required'], 
                'tribes'                => ['required'],
                'height'                => ['required'],
                'width'                 => ['required'],
            ];
            return $roles;
        }
        if ($this->step == 2) {
            return [
                // "identity_card"              => ['required'], 
                // "identity_card_number_ktp"   => ['required','numeric'], 
                // "validity_period_ktp"        => ['required', 'numeric'], 
                'pasport_number'                                => ['required'], 
                'passport_validity'                             => ['required'], 
                "drivers_license"                               => ['required'],
                "identity_card_number_sim"                      => ['required', 'numeric'], 
                "validity_period_sim"                           => ['required', 'numeric'], 
                "religion"                                      => ['required'], 
                "tribes"                                        => ['required'], 
                "citizenship"                                   => ['required'],
                "blood_group"                                   => ['required'],
                "height"                                        => ['required', 'numeric'], 
                "width"                                         => ['required', 'numeric'], 
                "kacamata"                                      => ['required'],
                "identity_card_number_bpjs_naker"               => ['required'],
                "validity_period_bpjs_naker"                    => ['required'],
                "identity_card_number_bpjs_kesehatan"           => ['required'],
                "validity_period_bpjs_kesehatan"                => ['required'],
                "identity_card_number_npwp"                     => ['required'],
                "validity_period_npwp"                          => ['required'],
                // "question1"                  => ['required'], 
                // "question2"                  => ['required'], 
            ];
        }

        if ($this->step == 3) {
            return [
                'father'                    => ['required'],
                'father_age'                => ['required', 'numeric'],
                'mother'                    => ['required'],
                'mother_age'                => ['required', 'numeric'],

                'sibling_name.*'            => ['required'],
                'sibling_age.*'             => ['required', 'numeric'],

                'child_name.*'              => ['required'],
                'child_age.*'               => ['required', 'numeric'],
            ];
        }

        if ($this->step == 4) {
            return [
                'formal_education_school_level.*'               => ['required'],
                'formal_education_nama_sekolah.*'               => ['required'],
                'formal_education_tempat_kota.*'                => ['required'],
                'formal_education_mulai.*'                      => ['required'],
                'formal_education_selesai.*'                    => ['required'],

                'course_training_jenis.*'                       => ['required'],
                'course_training_penyelenggara.*'               => ['required'],
                'course_training_tempat.*'                      => ['required'],
                'course_training_waktu.*'                       => ['required'],
                'course_training_dibiayai.*'                    => ['required'],

                'certificate_jenis.*'                           => ['required'],
                'certificate_penyelenggara.*'                   => ['required'],
                'certificate_tempat.*'                          => ['required'],
                'certificate_start.*'                           => ['required'],
                'certificate_end.*'                             => ['required'],
                'certificate_dibiayai.*'                        => ['required'],

                'language_ability_bahasa.*'                     => ['required'], 

                'Social_Activities_social_activities.*'         => ['required'],
                'Social_Activities_position.*'                  => ['required'],
                'Social_Activities_city.*'                      => ['required'],
                'Social_Activities_start.*'                     => ['required'],
                'Social_Activities_end.*'                       => ['required'],

                'Leisure_Activities_Leisure_Activities.*'       => ['required'],
                'Leisure_Activities_active.*'                   => ['required'],
                'Leisure_Activities_passive.*'                  => ['required'],
            ];
        }

        if ($this->step == 5) {
            return [
                'employee_history_start_bln.*'                        => ['required'], 
                'employee_history_start_thn.*'                        => ['required'], 
                'employee_history_start_gaji.*'                       => ['required', 'numeric'], 
                'employee_history_start_tunjangan.*'                  => ['required'], 
                'employee_history_start_posisi_jabatan.*'             => ['required'], 
                'employee_history_finish_bln.*'                       => ['required'], 
                'employee_history_finish_thn.*'                       => ['required'], 
                'employee_history_finish_gaji.*'                      => ['required', 'numeric'], 
                'employee_history_finish_tunjangan.*'                 => ['required'], 
                'employee_history_finish_posisi_jabatan.*'            => ['required'], 
                'employee_history_company_name_and_address.*'         => ['required'], 
                'employee_history_jenis_usaha.*'                      => ['required'], 
                'employee_history_alasan_berhenti.*'                  => ['required'], 
                'employee_history_gambaran_singkat.*'                 => ['required'], 
                'employee_history_gambaran_struktur_organisasi.*'     => ['required'],
            ];
        }

        if ($this->step == 6) {
            return [
                'gaji'                                      => ['required'],
                'question3'                                 => ['required'],
                'question4'                                 => ['required'],
                'ref_1_tab_6_hubungan'                      => ['required'],
                'ref_1_tab_6_nama'                          => ['required'],
                'ref_1_tab_6_alamat'                        => ['required'],
                'ref_1_tab_6_telp'                          => ['required'],
                'ref_1_tab_6_pekerjaan_pendidikan'          => ['required'],
                'ref_2_tab_6_hubungan'                      => ['required'],
                'ref_2_tab_6_nama'                          => ['required'],
                'ref_2_tab_6_alamat'                        => ['required'],
                'ref_2_tab_6_telp'                          => ['required'],
                'ref_2_tab_6_pekerjaan_pendidikan'          => ['required'],
                'ref_3_tab_6_nama'                          => ['required'],
                'ref_3_tab_6_alamat'                        => ['required'],
                'ref_3_tab_6_hubungan'                      => ['required'],
                'ref_3_tab_6_telp'                          => ['required'],
                'ref_4_tab_6_nama'                          => ['required'],
                'ref_4_tab_6_alamat'                        => ['required'],
                'ref_4_tab_6_hubungan'                      => ['required'],
                'ref_4_tab_6_telp'                          => ['required'],
            ];
        }
        return [];
    }

    public function messages()
    {
        return [
            'required'  => lang('The :attribute field is required'),
            'email'     => lang('The :attribute must be a valid email address'),
            'numeric'   => lang('The :attribute must be a number'),
        ];
    }
    

}
