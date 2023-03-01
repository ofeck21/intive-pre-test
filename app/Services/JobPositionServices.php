<?php
namespace App\Services;

use App\Http\Resources\CountriesResource;
use App\Models\JobPosition;

class JobPositionServices 
{
    protected $jobPositionModel;

    public function __construct() {
        $this->jobPositionModel = new JobPosition();
    }

    public function getAll()
    {
        $data = $this->jobPositionModel->get();
        return CountriesResource::collection($data);
    }
}