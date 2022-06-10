<?php

namespace App\Models;

use App\Filters\SystemMessageFilter;
use EloquentFilter\Filterable;

class SystemMessage extends BaseModel
{
    use Filterable;

    protected $fillable = [
        'type',//消息类型:1:通知,2:营销
        'user_ids',
        'title',
        'content',
        'client_type',//消息类型:1:安卓,2:ios,0:全部
        'status',//状态:pending:发送中，success:发送成功,failed:发送失败
        'image_url',
        'push_time'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $table = 'system_messages';

    public function modelFilter(): string
    {
        return $this->provideFilter(SystemMessageFilter::class);
    }
}
