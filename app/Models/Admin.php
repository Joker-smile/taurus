<?php

namespace App\Models;

use App\Filters\AdminFilter;
use EloquentFilter\Filterable;

class Admin extends BaseModel
{
    use Filterable;

    protected $fillable = [
        'nick_name',
        'phone',
        'avatar',
        'password',
        'depart_id',
        'position_id',
        'role_id',
        'status',//active,inactive
        'is_delete',
        'token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $table = 'admins';

    public function position()
    {
        return $this->hasOne(Department::class, 'id', 'position_id');
    }

    public function department()
    {
        return $this->hasOne(Department::class, 'id', 'depart_id');
    }

    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(AdminFilter::class);
    }
}
