<?php

namespace App\Models;

class City extends BaseModel
{
    protected $fillable = [
        'name', 'city_code', 'province_code'
    ];

    protected $table = 'city';

    public function area()
    {
        return $this->hasMany(Area::class, 'city_code', 'city_code');
    }
}
