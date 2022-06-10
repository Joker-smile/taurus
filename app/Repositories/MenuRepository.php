<?php


namespace App\Repositories;


use App\Models\Menu;

class MenuRepository extends AbstractRepository
{

    public function model(): string
    {
        return Menu::class;
    }
}
