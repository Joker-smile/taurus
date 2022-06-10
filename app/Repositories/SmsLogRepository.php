<?php

namespace App\Repositories;

use App\Models\SmsLog;

class SmsLogRepository extends AbstractRepository
{

    public function model(): string
    {
        return SmsLog::class;
    }
}
