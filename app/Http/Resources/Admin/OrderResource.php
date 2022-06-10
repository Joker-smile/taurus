<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'rent_number' => $this->rent_number,
            'lessee_number' => $this->lessee_number,
            'lessor_number' => $this->lessor_number,
            'produce_number' => $this->produce_number,
            'lessee_settle_number' => $this->lessee_settle_number,
            'lessor_settle_number' => $this->lessor_settle_number,
            'lessee_finance_number' => $this->lessee_finance_number,
            'lessor_finance_number' => $this->lessor_finance_number,
            'status' => $this->status,
            'project_name' => $this->project_name,
            'lessee_name' => $this->lessee ? $this->lessee->real_name : '',//承租方
            'lessee_id' => $this->lessee_id,//承租方id
            'device_type_id' => $this->device_type_id,
            'device_group_id' => $this->device_group_id,
            'construction_site' => $this->construction_site,
            'brand_name' => $this->brand_name,
            'device_name' => $this->device_name,
            'specification_model' => $this->specification_model,
            'new_rate' => $this->new_rate,
            'entry_time' => $this->entry_time,
            'construction_value' => $this->construction_value,
            'construction_unit' => $this->construction_unit,
            'created_at' => $this->created_at ? (string)$this->created_at : '',
            'other_request' => $this->other_request,
            'maker' => $this->maker ? $this->maker->nick_name : '',//制表人
            'reviewer' => $this->reviewer ? $this->reviewer->nick_name : '',//审核人
            're-reviewer' => $this->ReReviewer ? $this->ReReviewer->nick_name : '',//复审核人
            'review_time' => $this->review_time ? (string)$this->review_time : '',//审核时间
            'release_time' => $this->release_time ? (string)$this->release_time : '',
            'lessor_name' => $this->lessor ? $this->lessor->real_name : '',//出租方
            'lessor_id' => $this->lessor_id,//出租方id
            'lessee_contract_number' => $this->lessee_contract_number,
            'lessor_contract_number' => $this->lessor_contract_number,
            'service_rate' => $this->service_rate,
            'device_quantity' => $this->device_quantity,
            'construction_images' => $this->construction_images ?? '',
            'produce_start_time' => $this->produce_start_time,
            'produce_end_time' => $this->produce_end_time,
            'subsidiary' => $this->subsidiary,
            'review_message' => $this->review_message ?? '',
            'remark' => $this->remark,
            'salesman' => $this->salesman ?? '平台',
            'lessor' => $this->lessor ? $this->lessor : '',
            'company_name' => $this->lessee ? $this->lessee->company_name : '',
            'updated_at' => (string)$this->updated_at,

            //出租方金额相关
            'lessor_rental_price' => format_money($this->rental_price),//出租方租金单价
            'lessor_deduction_price' => format_money($this->lessor_deduction_price),//出租方扣款金额
            'lessor_deduction_description' => $this->lessor_deduction_description ?? '',//出租方奖扣描述
            'lessor_total_unit_price' => format_money($this->lessor_total_unit_price),//出租方总单价
            'lessor_total_price' => format_money($this->lessor_total_price),//出租方总金额
            'lessor_rent_amount' => format_money($this->lessor_rent_amount),//出租方租赁金额

            //承租方金额相关
            'lessee_rental_price' => format_money($this->lessee_rental_price),//承租租方租金单价
            'lessee_total_price' => format_money($this->lessee_total_price),//承租方总金额
            'lessee_deduction_price' => format_money($this->lessee_deduction_price),//承租方扣款金额
            'lessee_deduction_description' => $this->lessee_deduction_description ?? '',//承租奖扣款描述
            'lessee_rent_amount' => format_money($this->lessee_rent_amount),//承租方租赁金额

            //其他金额
            'rental_total' => format_money($this->rental_total),//租金金额(求租填)
            'lump_sum_price' => format_money($this->lump_sum_price),//包干单价
            'service_charge' => format_money($this->service_charge),//服务费
            'cost_price' => format_money($this->cost_price),//费用金额
        ];

        if ($this->type == 'lease') {
            $data['rent_order'] = $this->rent ? new OrderResource($this->rent) : '';
        }

        if ($this->type == 'finance') {
            $data = array_merge($data, [
                'receive_amount' => format_money($this->payments()->where('type', 'receive')->sum('amount')),
                'payment_amount' => format_money($this->payments()->where('type', 'payment')->sum('amount')),
            ]);
        }

        return $data;
    }
}
