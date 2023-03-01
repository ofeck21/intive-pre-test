<?php
namespace App\Services;

use App\Http\Resources\OptionsResource;
use App\Models\Options;

class OptionServices 
{
    protected $optionsModel;

    public function __construct() {
        $this->optionsModel = new Options();
    }

    public function getAll()
    {
        $data = $this->optionsModel->get();
        return OptionsResource::collection($data);
    }
}