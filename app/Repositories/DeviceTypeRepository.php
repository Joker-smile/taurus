<?php

namespace App\Repositories;

use App\Models\DeviceType;

class DeviceTypeRepository extends AbstractRepository
{
    public function model(): string
    {
        return DeviceType::class;
    }
}
