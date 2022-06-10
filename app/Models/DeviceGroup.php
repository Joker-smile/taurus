<?php

namespace App\Models;

class DeviceGroup extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',//0:不启用,1:启用
        'type_id'//设备类型id
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $appends = ['number'];

    protected $table = 'device_groups';

    public function devices()
    {
        return $this->hasMany(DeviceName::class, 'group_id', 'id');
    }

    public function getNumberAttribute()
    {
        return deviceNumber($this->id);
    }
}
