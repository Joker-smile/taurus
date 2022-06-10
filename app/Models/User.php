<?php

namespace App\Models;

use App\Filters\UserFilter;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone',
        'password',
        'real_name',
        'id_card_number',
        'certify_id',
        'bank_name',
        'account_number',
        'status',//reviewing,active,inactive
        'avatar',
        'device_token',
        'business_license',//营业执照
        'company_name',
        'credit_code',
        'company_address',
        'id_card_face',
        'id_card_back',
        'client_type',
        'type',//用户类型:lessor:出租方，lessee:承租方
        'remark',
        'bank_license',//开户行许可证
        'register_capital',//注册资本
        'employees',//从业人数
        'review_message',
        'admin_id',
        'province',
        'city',
        'area',
        'address',
        'invoicing_phone'
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

    protected $appends = ['number'];

    protected $table = 'users';

    public function modelFilter(): string
    {
        return $this->provideFilter(UserFilter::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class, 'id', 'admin_id');
    }

    public function getNumberAttribute()
    {
        if ($this->type == 'lessee') {
            return 'S' . getUserNumber($this->province, $this->city, $this->id);
        }

        if (!$this->province || !$this->city) {
            return 'C' . getUserNumber('福建省', '泉州市', $this->id);
        }

        return 'C' . getUserNumber($this->province, $this->city, $this->id);
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return Carbon::instance($date)->toDateTimeString();
    }
}
