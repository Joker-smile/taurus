<?php

namespace App\Models;

use App\Filters\ContractFilter;
use EloquentFilter\Filterable;

class Contract extends BaseModel
{
    use Filterable;

    protected $fillable = [
        'user_id',
        'admin_id',
        'type',
        'contract_number',
        'sign_year',
        'start_time',
        'end_time',
        'content'
    ];

    protected $table = 'contracts';

    protected $casts = [
        'content' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(ContractFilter::class);
    }

}
