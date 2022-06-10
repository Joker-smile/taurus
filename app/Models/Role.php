<?php

namespace App\Models;

use App\Filters\RoleFilter;
use EloquentFilter\Filterable;

class Role extends BaseModel
{
    use Filterable;

    protected $fillable = [
        'name',
        'status'//active,inactive
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $table = 'roles';

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menus', 'role_id', 'menu_id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(RoleFilter::class);
    }
}
