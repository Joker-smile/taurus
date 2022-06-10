<?php

namespace App\Models;

class Menu extends BaseModel
{
    protected $fillable = [
        'name',
        'pid',
        'path'
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $table = 'menus';

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menus', 'menu_id', 'role_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'pid', 'id');
    }

    public function allChildren()
    {
        return $this->children()
            ->with(['roles', 'allChildren'])
            ->orderBy('sort', 'asc');
    }
}
