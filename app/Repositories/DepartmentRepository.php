<?php


namespace App\Repositories;


use App\Models\Department;

class DepartmentRepository extends AbstractRepository
{

    public function model(): string
    {
        return Department::class;
    }
}
