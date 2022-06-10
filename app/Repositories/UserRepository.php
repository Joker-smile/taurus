<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends AbstractRepository
{
    public function model(): string
    {
        return User::class;
    }
}
