<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'type' => $this->type ? $this->type->name : '',
            'group' => $this->group ? $this->group->name : '',
            'type_id' => $this->type_id,
            'group_id' => $this->group_id,
            'lessor_name' => $this->user ? $this->user->real_name : '',
            'lessor_company_address' => $this->user ? $this->user->company_address : '',
            'specification_model' => $this->specification_model,
            'brand_name' => $this->brand_name,
            'license_number' => $this->license_number,
            'new_rate' => $this->new_rate,
            'remark' => $this->remark,
            'status' => $this->status,
            'images' => $this->images,
            'number' => $this->number,
            'review_time' => (string)$this->review_time,
            'reviewer' => $this->reviewer ? $this->reviewer->nick_name : '',
            'review_message' => $this->review_message
        ];
    }
}
