<?php

namespace App\Models;

use App\Filters\DeviceFilter;
use EloquentFilter\Filterable;

class Device extends BaseModel
{
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type_id',
        'group_id',
        'name',
        'specification_model',
        'brand_name',
        'license_number',
        'new_rate',
        'remark',
        'status',//reviewing,success,failed
        'images',
        'number',//编号
        'review_time',
        'review_admin_id',
        'is_delete',
        'review_message'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'images' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'review_time' => 'datetime:Y-m-d H:i:s',
    ];

    protected $hidden = [
        'is_delete', 'review_admin_id', 'user_id'
    ];

    protected $table = 'devices';

    public function modelFilter(): string
    {
        return $this->provideFilter(DeviceFilter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(DeviceType::class, 'type_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(DeviceGroup::class, 'group_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'review_admin_id', 'id');
    }

}
