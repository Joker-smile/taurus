<?php

namespace App\Models;

use App\Filters\FeedbackFilter;
use EloquentFilter\Filterable;

class Feedback extends BaseModel
{
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',//1：功能需求，2：BUG，3：产品体验，4：其他
        'describe',
        'images',
        'client_type'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'images' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $table = 'feedback';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(FeedbackFilter::class);
    }
}
