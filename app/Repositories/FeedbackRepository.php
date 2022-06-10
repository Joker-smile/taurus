<?php

namespace App\Repositories;

use App\Models\Feedback;

class FeedbackRepository extends AbstractRepository
{
    public function model(): string
    {
        return Feedback::class;
    }
}
