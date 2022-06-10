<?php

namespace App\Models;

use App\Filters\PaymentFilter;
use EloquentFilter\Filterable;

class Payment extends BaseModel
{
    use Filterable;

    protected $fillable = [
        'order_id',
        'payer',
        'bank_name',
        'account',
        'amount',
        'remark',
        'review_id',
        're-review_id',
        'make_id',
        'status',//状态:pending:等待审核,reviewing:审核中,re-reviewing:复核中,review_success:审核通过,review_failed:审核失败
        'surplus',
        'lessee_id',
        'lessor_id',
        'payment_number',
        'collection_number',
        'type'//receive:收款,payment:付款
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $table = 'payments';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function maker()
    {
        return $this->belongsTo(Admin::class, 'make_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'review_id', 'id');
    }

    public function reReviewer()
    {
        return $this->belongsTo(Admin::class, 're-review_id', 'id');
    }

    public function modelFilter(): string
    {
        return $this->provideFilter(PaymentFilter::class);
    }

    /**
     * 出租方
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lessor()
    {
        return $this->hasOne(User::class, 'id', 'lessor_id');
    }

    /**
     * 承租方
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lessee()
    {
        return $this->hasOne(User::class, 'id', 'lessee_id');

    }
}
