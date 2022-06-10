<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id' => $this->order_id,
            'type' => $this->type,
            'lessee_number' => $this->order ? $this->order->lessee_number : '',
            'lessor_number' => $this->order ? $this->order->lessor_number : '',
            'lessee_name' => $this->lessee ? $this->lessee->real_name : '',
            'lessor_name' => $this->lessor ? $this->lessor->real_name : '',
            'company_name' => $this->lessee ? $this->lessee->company_name : '',
            'amount' => format_money($this->amount),
            'surplus' => format_money($this->surplus),
            'created_at' => (string)$this->created_at,
        ];

        if (in_array($this->type, ['payment', 'receive'])) {
            $data = array_merge($data, [
                'payment_number' => $this->payment_number,
                'collection_number' => $this->collection_number,
                'payer' => $this->payer,
                'bank_name' => $this->bank_name,
                'account' => $this->account,
                'amount_u' => number2chinese(format_money($this->amount), true),
                'status' => $this->status,
                'remark' => $this->remark,
                'reviewer' => $this->reviewer ? $this->reviewer->nick_name : '',
                're-reviewer' => $this->reReviewer ? $this->reReviewer->nick_name : '',
                'maker' => $this->maker ? $this->maker->nick_name : '',
            ]);
        }

        return $data;
    }
}
