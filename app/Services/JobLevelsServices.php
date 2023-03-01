<?php
namespace App\Services;

use App\Http\Resources\CountriesResource;
use App\Models\JobLevel;

class JobLevelsServices 
{
    protected $jobLevelsModel;

    public function __construct() {
        $this->jobLevelsModel = new JobLevel();
    }

    public function getAll()
    {
        $data = $this->jobLevelsModel->get();
        return CountriesResource::collection($data);
    }
}