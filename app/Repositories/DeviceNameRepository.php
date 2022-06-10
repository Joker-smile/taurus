<?php

namespace App\Repositories;

use App\Models\DeviceName;

class DeviceNameRepository extends AbstractRepository
{
    public function model(): string
    {
        return DeviceName::class;
    }
}
