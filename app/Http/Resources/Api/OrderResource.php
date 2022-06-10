<?php

namespace App\Http\Resources\Api;

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
        $receive_amount = $this->payments()->where('status', 'review_success')->where('type', 'payment')->sum('amount');
        $data = [
            'id' => $this->id,
            'lessor_number' => $this->lessor_number,
            'lessor_id' => $this->lessor_id,
            'lessee_name' => $this->lessee ? $this->lessee->company_name : '',//承租方
            'lessor_name' => $this->lessor ? $this->lessor->real_name : '',//出租方
            'project_name' => $this->project_name,
            'construction_site' => $this->construction_site,
            'lessor_contract_number' => $this->lessor_contract_number,
            'brand_name' => $this->brand_name,
            'device_quantity' => $this->device_quantity,
            'device_name' => $this->device_name,
            'device_number' => $this->device ? $this->device->number : '',
            'specification_model' => $this->specification_model,
            'construction_unit' => $this->construction_unit,
            'construction_value' => $this->construction_value,
            'rental_price' => format_money($this->rental_price),//租金单价
            'rent_amount' => format_money($this->lessor_rent_amount),//出租方租赁金额
            'lump_sum_price' => format_money($this->lump_sum_price),//包干单价
            'cost_price' => format_money($this->cost_price),//费用金额
            'total_unit_price' => format_money($this->lessor_total_unit_price),//总单价
            'lessor_total_price' => format_money($this->lessor_total_price),//出租方总金额
            'deduction_price' => format_money($this->lessor_deduction_price ?? 0),//扣款金额
            'deduction_description' => $this->lessor_deduction_description,
            'total' => format_money($this->lessor_total_price + $this->lessor_deduction_price),
            'total_u' => number2chinese(format_money($this->lessor_total_price + $this->lessor_deduction_price), true),
            'receive_payment' => format_money($receive_amount),
            'remaining_payment' => format_money($this->lessor_total_price + $this->lessor_deduction_price - $receive_amount),
            'updated_at' => (string)$this->updated_at,

            'entry_time' => $this->entry_time,
            'lessee_number' => $this->lessee_number,
            'review_message' => $this->review_message ?? '',
            'maker' => $this->maker ? $this->maker->nick_name : '',//制表人
            'reviewer' => $this->reviewer ? $this->reviewer->nick_name : '',//审核人
            'review_time' => $this->review_time ? (string)$this->review_time : '',//审核时间
            'other_request' => $this->other_request,
            'salesman' => $this->salesman ?? '平台',
        ];

        $data['status'] = 'processing';
        if ($this->type == 'finance') {
            $data['status'] = 'finished';
        }

        if ($this->type == 'finance' && !$this->payments()->where('type', 'payment')->get()->isEmpty()) {
            $data['status'] = 'settling';
            if ($receive_amount == ($this->lessor_total_price + $this->lessor_deduction_price)) {
                $data['status'] = 'settled';
            }
        }

        return $data;
    }
}
