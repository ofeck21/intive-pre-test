<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeesWorkExperienceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                                => $this->id,
            'start_month'                       => new OptionsResource($this->startMonth),
            'start_year'                        => $this->start_year,
            'start_salary'                      => $this->start_salary,
            'start_subsidy'                     => $this->start_subsidy,
            'start_position'                    => $this->start_position,

            'finish_month'                      => new OptionsResource($this->finishMonth),
            'finish_year'                       => $this->finish_year,
            'finish_salary'                     => $this->finish_salary,
            'finish_subsidy'                    => $this->finish_subsidy,
            'finish_position'                   => $this->finish_position,

            'company_name_and_address'          => $this->company_name_and_address,
            'type_of_business'                  => $this->type_of_business,

            'reason_to_stop'                    => $this->reason_to_stop,
            'brief_overview'                    => $this->brief_overview,
            'position_struktur_organisasi'      => $this->position_struktur_organisasi,
        ];
    }
}
