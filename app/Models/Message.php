<?php

namespace App\Models;

class Message extends BaseModel
{
    protected $fillable = [
        'type',
        'user_id',
        'title',
        'content',
        'order_id',
        'order_data',
        'is_read',
        'is_delete',
        'system_message_id',
        'image_url'
    ];
    protected $casts = [
        'order_data' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $table = 'messages';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
