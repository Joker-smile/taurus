<?php


namespace App\Repositories;


use App\Models\Role;

class RoleRepository extends AbstractRepository
{
    public function model(): string
    {
        return Role::class;
    }
}
