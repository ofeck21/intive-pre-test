<?php
namespace App\ResponseServices;

class ResponseService
{
    public static function toArray($data)
    {
        return json_decode($data);
    }
}
