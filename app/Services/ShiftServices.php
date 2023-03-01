<?php
namespace App\Services;

use App\Http\Resources\ShiftResource;
use App\Models\Shift;

class ShiftServices 
{
    protected $shiftModel;

    public function __construct() {
        $this->shiftModel = new Shift();
    }

    public function getAll()
    {
        $data = $this->shiftModel->get();
        return ShiftResource::collection($data);
    }
}