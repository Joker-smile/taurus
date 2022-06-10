<?php

namespace App\Models;

class Province extends BaseModel
{
    protected $fillable = [
        'name', 'province_code'
    ];

    protected $table = 'province';

    public function city()
    {
        return $this->hasMany(City::class, 'province_code', 'province_code');
    }
}
