<?php
namespace App\Services;

use App\Http\Resources\CountriesResource;
use App\Models\Company;
use App\Models\Countries;

class CompanyServices 
{
    protected $companyModel;

    public function __construct() {
        $this->companyModel = new Company();
    }

    public function getAll()
    {
        $data = $this->companyModel->get();
        return CountriesResource::collection($data);
    }
}