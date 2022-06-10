<?php

namespace App\Models;

use App\Filters\OrderFilter;
use EloquentFilter\Filterable;

class Order extends BaseModel
{
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rent_id',//求租订单id
        'lease_id',//租赁订单id
        'produce_id',//生产订单id
        'settle_id',//结算订单id
        'review_id',//审核人id
        're-review_id',//复核人id
        'make_id',//制表人
        'device_id',//设备id
        'lessee_id',//承租方id
        'lessor_id',//出租方id
        'device_type_id',//设备类型id
        'device_group_id',//设备组别id
        'project_name',//工程名称
        'construction_site',//施工地
        'lessee_contract_number',//承租方合同编号
        'lessor_contract_number',//出租方合同编号
        'construction_unit',//施工单位
        'rental_price',//租赁单价
        'rental_total',//租赁金额
        'lump_sum_price',//包干单价
        'other_request',//其他要求
        'new_rate',//成新率
        'type',//类型:rent:求租订单,lease:租赁订单,produce:生产订单,settle:结算订单,finance:财务订单
        'status',//状态:pending:等待发布/分配,reviewing:审核中,re-reviewing:复核中,review_success:审核通过,review_failed:审核失败,invalid:无效
        'release_time',//发布时间
        'review_time',//审核时间
        'brand_name',//品牌名称
        'device_name',//设备名称
        'specification_model',//设备型号
        'entry_time',//进场时间
        'service_rate',//服务费率
        'is_delete',
        'device_quantity',//设备数量
        'construction_value',//施工量
        'produce_start_time',//开始作业时间
        'produce_end_time',//结束作业时间
        'construction_images',//现场作业图
        'lessor_deduction_description',//出租方扣款描述
        'lessee_deduction_description',//承租方扣款描述
        'lessor_deduction_price',//出租方扣款金额
        'lessee_deduction_price',//承租方扣款金额
        'subsidiary',//子公司
        'review_message',//审核信息
        'remark',//备注
        'salesman',//业务员名称

        'rent_number',//求租编号
        'lessee_number',//承租方租赁编号
        'lessor_number',//出租方租赁编号
        'produce_number',//生产编号
        'lessee_settle_number',//承租方结算编号
        'lessor_settle_number',//出租方结算编号
        'lessee_finance_number',//承租方应收款编号
        'lessor_finance_number',//出租方应付款编号
    ];

    protected $casts = [
        'construction_images' => 'array'
    ];

    protected $table = 'orders';

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

    public function modelFilter(): string
    {
        return $this->provideFilter(OrderFilter::class);
    }

    public function maker()
    {
        return $this->belongsTo(Admin::class, 'make_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'review_id', 'id');
    }

    public function ReReviewer()
    {
        return $this->belongsTo(Admin::class, 're-review_id', 'id');
    }

    public function rent()
    {
        return $this->belongsTo(Order::class, 'rent_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id', 'id');
    }

    public function device()
    {
        return $this->hasOne(Device::class, 'id', 'device_id');
    }

    /**
     * 出租方总金额(未计算扣款金额)
     * @return mixed
     */
    public function getLessorTotalPriceAttribute()
    {
        if ($this->type == 'lease') {
            return $this->lessor_rent_amount + $this->cost_price;
        }

        return $this->lessor_rent_amount;
    }

    /**
     * 承租方总金额(未计算扣款金额)
     * @return mixed
     */
    public function getLesseeTotalPriceAttribute()
    {
        return $this->lessee_rent_amount + $this->service_charge;
    }

    /**
     * 费用金额
     * @return float|int
     */
    public function getCostPriceAttribute()
    {
        return $this->construction_value * $this->lump_sum_price;
    }

    /**
     * 服务费
     * @return float|int
     */
    public function getServiceChargeAttribute()
    {
        return $this->service_rate * ($this->lessee_rent_amount + $this->lessee_deduction_price) / 100;
    }

    /**
     * 出租方租赁金额
     * @return float|int
     */
    public function getLessorRentAmountAttribute()
    {
        return $this->rental_price * $this->construction_value;
    }

    /**
     * 承租方租赁金额
     * @return float|int
     */
    public function getLesseeRentAmountAttribute()
    {
        return $this->lessee_rental_price * $this->construction_value;
    }

    /**
     * 承租方租赁单价
     * @return float|int
     */
    public function getLesseeRentalPriceAttribute()
    {
        if ($this->type != 'rent' && $rent_order = $this->rent) {

            return $rent_order->rental_price;
        }

        return $this->rental_price;
    }

    /**
     * 出租方总单价
     * @return float|int
     */
    public function getLessorTotalUnitPriceAttribute()
    {
        return $this->rental_price + $this->lump_sum_price;
    }

}
