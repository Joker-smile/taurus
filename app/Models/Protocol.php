<?php

namespace App\Models;

class Protocol extends BaseModel
{
    protected $fillable = [
        'key',
        'value'
    ];

    protected $table = 'protocols';

    public $timestamps = false;
}
