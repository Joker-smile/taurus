<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository extends AbstractRepository
{
    public function model(): string
    {
        return Payment::class;
    }
}
