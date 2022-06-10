<?php


namespace App\Repositories;


use App\Models\Device;

class DeviceRepository extends AbstractRepository
{

    public function model(): string
    {
        return Device::class;
    }
}
