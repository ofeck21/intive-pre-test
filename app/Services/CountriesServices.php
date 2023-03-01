<?php
namespace App\Services;

use App\Http\Resources\CountriesResource;
use App\Models\Countries;

class CountriesServices 
{
    protected $countriesModel;

    public function __construct() {
        $this->countriesModel = new Countries();
    }

    public function getAll()
    {
        $data = $this->countriesModel->get();
        return CountriesResource::collection($data);
    }
}