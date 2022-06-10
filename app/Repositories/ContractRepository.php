<?php

namespace App\Repositories;

use App\Models\Contract;

class ContractRepository extends AbstractRepository
{
    public function model(): string
    {
        return Contract::class;
    }
}
