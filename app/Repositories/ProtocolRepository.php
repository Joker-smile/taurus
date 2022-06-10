<?php


namespace App\Repositories;


use App\Models\Protocol;

class ProtocolRepository extends AbstractRepository
{
    public function model(): string
    {
        return Protocol::class;
    }
}
