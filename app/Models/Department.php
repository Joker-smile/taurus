<?php

namespace App\Models;

use App\Filters\DepartmentFilter;
use EloquentFilter\Filterable;

class Department extends BaseModel
{
    use Filterable;

    protected $fillable = [
        'pid',
        'depart_name',
        'position_name',
        'status'//active,inactive
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $table = 'departments';

    public function position()
    {
        return $this->hasMany(Department::class, 'pid', 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'pid', 'id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(DepartmentFilter::class);
    }
}
