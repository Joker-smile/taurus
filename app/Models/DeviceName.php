<?php

namespace App\Models;

class DeviceName extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',//0:不启用,1:启用
        'group_id'//设备组别id
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $appends = ['number'];

    protected $table = 'device_names';

    public function getNumberAttribute()
    {
        return deviceNumber($this->id);

    }
}
