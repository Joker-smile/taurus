<?php

namespace App\Models;

use App\Filters\DeviceTypeFilter;
use EloquentFilter\Filterable;

class DeviceType extends BaseModel
{
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',//0:不启用,1:启用
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $appends = ['number'];

    protected $table = 'device_types';

    public function groups()
    {
        return $this->hasMany(DeviceGroup::class, 'type_id', 'id');
    }

    public function getNumberAttribute()
    {
        return deviceNumber($this->id);
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(DeviceTypeFilter::class);
    }
}
