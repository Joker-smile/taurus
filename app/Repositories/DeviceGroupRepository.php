<?php

namespace App\Repositories;

use App\Models\DeviceGroup;

class DeviceGroupRepository extends AbstractRepository
{
    public function model(): string
    {
        return DeviceGroup::class;
    }
}
