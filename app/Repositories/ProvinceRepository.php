<?php

namespace App\Repositories;

use App\Models\Province;

class ProvinceRepository extends AbstractRepository
{
    public function model(): string
    {
        return Province::class;
    }
}
