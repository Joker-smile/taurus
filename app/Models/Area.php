<?php

namespace App\Models;

class Area extends BaseModel
{
    protected $fillable = [
        'name', 'area_code', 'city_code'
    ];

    protected $table = 'area';
}
